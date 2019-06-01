# Detailed Notes

Recently, I was challenged with figuring out how to setup Drupal 8 on Azure. This was to help correct a client site that struggled with deployments. The existing setup was run on Azure hosting, using a Windows-based App Service. The deployment script itself was using a very old Windows scripting method (pre-Power Shell). The stage instance was underpowered (free tier) and the prod instance was a bit overpowered.

What led to the hosting switch was a combination of things, including the need to update the website repeatedly and deployments that consistently failed for random reasons. Failures that would happen at different points in the deployment process.

For those familiar with Drupal, it is normally hosted on a Linux environment. Drupal development is often designed around this concept. With modules and packages sometimes (though rarely) even requiring OS features and libraries specific to Linux. In general, we have found it just works better in Linux. It is built in the PHP programming language that can technically run in any operating system. However, being both open source and more often than not used in a Linux environment, things tend to be tuned better for Linux than Windows.

The client, for their part, is pretty tied to Azure and I believe Microsoft online products in general. They wished to continue hosting with Azure. With some research (and a bit of help from Karl Kedrovsky), I figured out the steps to setup the Linux equivalent of what the currently had.

Because the client ultimately wanted to control the Azure resources, I created the following step-by-step for them to follow.

### Step-By-Step Resource Setup Guide

#### **Before you begin**

- The pricing tiers selected below are appropriate for the stage environment.
- For production we will want to give the site more resources for the "App Service Plan" and the database.
- In time you may adjust these plans for production to suite your needs.

#### **First create your "App Service Plan"**

1. In the Azure Portal, click "Create a resource"
2. Type in "App Service Plan" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Put in the plan name, select your subscription, name the resource group you want to use for this (I suggest a new one just for this).
5. Select "Linux" as your operating system.
6. Select pricing tier "B1 Basic" and click "Create"

#### **Then create your web app**

1. In the Azure Portal, click "Create a resource"
2. Type in "Web" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Enter the App Name, for Resource Group select "Use existing" and select the same one as your App Service Plan.
5. Select the existing App Service Plan.
6. For "OS" select "Linux" and for "Runtime Stack" select "PHP 7.2".
7. Click "Create".

#### **Then your database**

1. In the Azure Portal, click "Create a resource"
2. Type in "Azure Database for MySQL" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Enter server name of your choice, select the same resource group as above.
5. Enter a custom username and password.
6. Change pricing tier to "Basic, 1 vCore(s), 50GB" (should be fine for now).
7. Click "Create".

After the client setup the new stage and production environments I had a few more challenges I ran into. The first was expected. The client hosts their own git repository that was severely unpowered and often got overwhelmed by just cloning the repository down. To resolve that I created a new, free, private repository on Bitbucket and gave them full administrative rights.

The second problem was very unexpected. From the beginning it has appeared that Azure is highly configurable for any setup you wish to use. Unfortunately, in practice this breaks down when you are no longer using a Windows environment. The main issue being with the Apache configuration. It did not let me specify a document root separate from the base deployment directory. Given I was limited on time I was supposed to spend on this I could not spin my wheels for too long.

This might not sound like a big deal, but for those that work with Drupal, you know that the site itself isn't served from the project root, but instead from the "docroot" folder (or some other you specify in the configuration). I also did not want to restructure this Drupal site and preferred to keep it as conventional as possible.

I reached out to several people in TechOps, visiting some in person, and though we tried a number of avenues to change the configuration, it simply would not allow us to change this configuration. I had to work with this default Linux configuration (unless I defined my own Docker image and went down that rabbit hole).

Ultimately, I created a 3 part workaround that allowed me to serve a Drupal site from the Azure App Service Linux instance. First, I had to create a new ".htaccess" in the project root. Then I had to modify the ".htaccess" file in the "docroot" directory. Finally, I had to modify Drupal's "settings.php" file to accommodate this configuration.

### **What I did in the project**

1. Created a ".htaccess" file in the project root with the following in it.

```apacheconfig
<IfModule mod\_rewrite.c>
    RewriteEngine on
    RewriteRule ^$ docroot/$1 [L]
    RewriteCond %{REQUEST\_FILENAME} !-d
    RewriteCond %{REQUEST\_FILENAME} !-f
    RewriteRule ^(.\*)$ docroot/$1 [L]
<IfModule>
```

2. Modified the ".htaccess" in the "docroot" directory. Directly above the rewrite conditions there is a commented out "RewriteBase" directive. I uncommented this and set it to "RewriteBase /docroot".

3. I changed the "settings.php" to include this at the top:

```php
<?php
if (isset($GLOBALS['request']) && '/docroot/index.php' === $GLOBALS['request']->server->get('SCRIPT\_NAME')) {
  $GLOBALS['request']->server->set('SCRIPT\_NAME', '/index.php');
}
?>
```

This configuration works and works well. Finally, Azure is able to hook directly to Bitbucket and deploy from any changes pushed to a specified branch. Our stage environment is configured to pull from the "master" branch. The production environment pulls from the "production" branch.

###### **Bonus**

For testing of the changes I was making, I setup Docksal to run this locally. I had to make the following change in the "docksal.env" file: "DOCROOT=."

### **Other Things I Tried**

1. **App Service Configuration:**  I looked in the App Service Configuration. There are a number of articles online that talk about being able to change the document root. However, all of these were ultimately referring to Windows environments and not Linux. The Linux environments do not have this option.
2. **Configuring the path with the Azure CLI:**  As part of the Azure CLI, you can specify the directory the website is served from as separate from where your code is ultimately deployed to. I tried this several times and each time it didn't change anything at all.
3. **Changing the Apache config files:**  The very first thing I tried was to change the Apache config files like I normally would. I SSH'ed into the environment, changed the conf file, and restarted Apache. As soon as Apache restarts, it just wipes out all of my changes. This is probably due to this being an App Service setup. Which means the only part that persists are the files in the directory where everything is deployed to.
4. **Modifying the ".deployment" file:**  There is a project directive you can add to your ".deployment" file that is supposed to be for when the folder you want to deploy is not the root of your repository. I tried this as well and it had no effect.

### **Other Possible Approaches**

I was limited on hours I could spend on this project. Thus, I did not delve too deeply into alternative routes to get Drupal working on Azure. Here are some thoughts I had on alternative routes to get this working (and potential downsides).

- **Project Structure Change:**  There is a way (I believe) to change the structure of the Drupal project to have the index.php file directly in the project root (along with all of the other directories under "docroot"). However, this is a non-standard setup and could create some confusion for the next developer to come along. It would also clutter the project root. Ultimately, I decided that keeping the Drupal setup as standard as possible was the best route to take.
- **Custom Docker Image:**  You can setup Azure to run a custom Docker image. There are a lot of pre-existing images that could be used. However, this setup would have required a decent amount of research on my part and I already found a workable solution.
- **Do It With TerraForm:**  There is a tool called TerraForm that lets you store you infrastructure as code and setup everything very precisely in a set of configuration files. You can configure it for any cloud hosting service. For this client's needs this is absolutely overkill and would take precious time we did not have.
- **Try A 3rd Party Azure Image:**  When creating the setup instructions for the client, I did notice that there were 3rd party images available to choose from in the Azure marketplace. This might have been the next best approach to take and I seriously considered this route. However, it looked like it would spin up multiple containers for this and I was a bit unsure of the costs and ease of configuration. Especially of the database, which the client had ultimate control of currently and wanted to maintain that control. Thus I decided this route might complicate things more and would rather keep it as simple for the client as possible.

# Related Files
I have added related scripts and files to this repository.
