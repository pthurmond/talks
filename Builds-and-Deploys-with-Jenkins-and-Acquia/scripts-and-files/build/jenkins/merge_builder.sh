if [ "$build" == "" ]; then
  echo "ERROR: No build number defined."
  exit 1;
fi

if [ "$commit" == "" ]; then
  echo "ERROR: No commit hash defined."
  exit 1;
fi

cd /docksal/projects/ur

PROJECT="mystuff_dev"
PROJECT_BASE="/docksal/projects/mystuff/mystuff_dev"
GITREPO="git@bitbucket.org:mysite/myrepo.git"

if [ ! -d "$PROJECT" ]; then
  git clone ${GITREPO} ${PROJECT}
fi

cd ${PROJECT}

git remote | grep acquia || git remote add acquia useraccount@someserver.prod.hosting.acquia.com:unitedrentals.git

# Saving this method for later.
# git config lfs.https://someserver.prod.hosting.acquia.com/myproject.git/info/lfs.locksverify false
git clean -fd
git stash save -u

echo "Received Build '$build' and commit '$commit'."
BRANCH="build/mystuff/${build}_${commit}"
echo "Branch name will be ${BRANCH} upon build completion."

git checkout develop
git pull origin develop

git push acquia develop
git fetch acquia

git checkout acquia-develop
git pull acquia acquia-develop

git merge -X theirs develop -m 'merged'

rm -rf vendor/ docroot/modules/contrib/ docroot/libraries/
rm -rf docroot/themes/custom/mytheme/node_modules/ docroot/themes/custom/mytheme/vue/node_modules/
echo "Checkout of basics has been completed. Starting build..."

# set up local builder overrides and global composer cache
cp .docksal/docksal-local-builder.yml .docksal/docksal-local.yml
cp .docksal/docksal-local-builder.env .docksal/docksal-local.env

fin docker volume list | grep composer_cache || fin docker volume create composer_cache
fin up && fin init-deps builder

echo "Switching node versions..."
fin exec nvm install
fin exec nvm alias default $(cat .nvmrc)

if [ $? -ne 0 ]; then
    echo "Failed to switch node versions."
    exit 1
fi

cd docroot/themes/custom/mytheme

echo "Installing node_modules..."
fin exec npm run setup

if [ $? -ne 0 ]; then
    echo "Failed to install node_modules."
    exit 1
fi

echo "Building patternlab..."
fin exec npm run pl-postinstall

if [ $? -ne 0 ]; then
    echo "Failed to build patternlab."
    exit 1
fi

echo "Building theme..."
fin exec npm run theme:build

if [ $? -ne 0 ]; then
    echo "Failed to build theme."
    exit 1
fi

echo "Building JavaScript..."
fin exec npm run vue:build

if [ $? -ne 0 ]; then
    echo "Failed to build javascript."
    exit 1
fi

echo "Build processes have been completed. Adding to git..."
echo "Changing to path: ${PROJECT_BASE}"
cd ${PROJECT_BASE}

# Once built, we no longer need the node_modules directories
rm -rf docroot/themes/custom/mytheme/node_modules/ docroot/themes/custom/mytheme/vue/node_modules/

# Once patternlab is done - clear generated assets
rm -rf docroot/themes/custom/mytheme/pattern-lab/

gitSubmodules=$(fin exec sudo find docroot/modules/contrib -type d -name ".git" -print)
submodules=$(echo $gitSubmodules | sed -e 's/\/.git//g')

fin exec sudo find docroot/modules/contrib -type d -name ".git" -prune -exec rm -rf {} \;

### clear .git folder out of any composer-cloned modules or the `git add` will miss them.
fin exec find docroot/modules/contrib/ | grep "\.git/" | xargs rm -rf || :

# For some reason doing this again, locally, seems to finally remove the submodules
fin exec sudo find docroot/modules/contrib -type d -name ".git" -prune -exec rm -rf {} \;

while read -r brokenDir; do
  if [ ! -z "$brokenDir" ]
  then
    # Trim the whitespace
    CLEANED_DIR="$(echo -e "${brokenDir}" | tr -d '[:space:]')"

    echo "Cleaning directory $CLEANED_DIR"
    git rm -r --cached $CLEANED_DIR

    echo "Adding directory $CLEANED_DIR"
    git add -f $CLEANED_DIR
  fi
done <<< "$submodules"

# Make sure git does not add the docksal and local settings files
git rm docroot/sites/default/settings.docksal.php
git rm docroot/sites/default/settings.local.php

# Just in case git doesn't have this added we will remove it normally as well
rm docroot/sites/default/settings.docksal.php
rm docroot/sites/default/settings.local.php

git add . --force
git add -f vendor/
git add -f docroot/core/
git add -f docroot/modules/contrib/

git status
rm .git/hooks/pre-commit

echo "Third-party files added to git. Starting commit process..."

if git diff-index --quiet HEAD --; then
    echo "Nothing to commit."
else
  git commit -m "Adding compiled assets."
  echo "Compiled assets have been committed."
fi

fin stop

echo "Pushing to Acquia..."
git push acquia acquia-develop

echo "Checking if build branch exists..."
branch_exists=`git show-ref ${BRANCH}`

if [ -n "$branch_exists" ]; then
    echo "Build branch exists. Removing: ${BRANCH}"
    git branch -D $BRANCH
fi

echo "Creating build branch '$BRANCH'..."
git checkout -b $BRANCH

echo "Pushing build branch to Acquia..."
git push acquia $BRANCH

echo "Checking out develop once more...."
git checkout develop

echo "Deleting finished branches on build server."
git branch -D $BRANCH

rm -rf vendor/ docroot/modules/contrib/ docroot/libraries/
rm -rf docroot/themes/custom/mytheme/node_modules/ docroot/themes/custom/mytheme/vue/node_modules/

echo "Done!"