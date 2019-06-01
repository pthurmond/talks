#!/bin/bash
set -e

# BITBUCKET_BUILD_NUMBER: The unique identifier for a build. It increments with each build and can be used to create unique artifact names.
# BITBUCKET_COMMIT: The commit hash of a commit that kicked off the build.

### These two variables are passed in automatically...
# BITBUCKET_BUILD_NUMBER="$1"
# BITBUCKET_COMMIT="$2"

# Use shortened SHA1 hash
BITBUCKET_COMMIT=${BITBUCKET_COMMIT:0:7}

USER="some.person@example.com"
TOKEN="asdlfkj4f4_words_0isjs0950jg" # Not original, nor functional
JOB="job/MyGroup/job/Merge%20Builder"
JTOKEN="slkdjasoifiojfaj_something_akljfaosj0" # Not original, nor functional
PARAMS="token=$JTOKEN&build=${BITBUCKET_BUILD_NUMBER}&commit=${BITBUCKET_COMMIT}"

if [ "" == "${PARAMS}" ]; then
  NOTIFY_URL="${JOB}/build"
else
  NOTIFY_URL="${JOB}/buildWithParameters?${PARAMS}"
fi

CRUMB_ISSUER_URL='crumbIssuer/api/xml?xpath=concat(//crumbRequestField,":",//crumb)'

function notifyCI {
  CISERVER=$1

  echo "Loading crumb..."
  echo "curl --user ${USER}:${TOKEN} ${CISERVER}/${CRUMB_ISSUER_URL} 2>/dev/null"

  # Check if "[X] Prevent Cross Site Request Forgery exploits" is activated
  # so we can present a valid crumb or a proper header
  HEADER="Content-Type:text/plain;charset=UTF-8"
  CRUMB=$(curl --user ${USER}:${TOKEN} ${CISERVER}/${CRUMB_ISSUER_URL} 2>/dev/null)

  echo "Crumb is..."
  echo ${CRUMB}

  if [ "$CRUMB" != "" ]; then
    HEADER=$CRUMB
  fi

  echo "Triggering job..."
  echo "curl -X POST ${CISERVER}/${NOTIFY_URL} --header \"${HEADER}\" --user \"${USER}:${TOKEN}\""

  curl -X POST ${CISERVER}/${NOTIFY_URL} --header "${HEADER}" --user "${USER}:${TOKEN}"

  echo "Done"
}

# The code above was placed in a function so you can easily notify multiple Jenkins/Hudson servers:
notifyCI "https://jenkins.myrandomdomain.com"