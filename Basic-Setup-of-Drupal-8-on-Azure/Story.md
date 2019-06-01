# Story

Recently, I was challenged with figuring out how to setup Drupal 8 on Azure. This was to help correct a client site that struggled with deployments. The existing setup was run on Azure hosting, using a Windows-based App Service. The deployment script itself was using a very old Windows scripting method (pre-Power Shell). The stage instance was underpowered (free tier) and the prod instance was a bit overpowered.

What led to the hosting switch was a combination of things, including the need to update the website repeatedly and deployments that consistently failed for random reasons. Failures that would happen at different points in the deployment process.

For those familiar with Drupal, it is normally hosted on a Linux environment. Drupal development is often designed around this concept. With modules and packages sometimes (though rarely) even requiring OS features and libraries specific to Linux. In general, we have found it just works better in Linux. It is built in the PHP programming language that can technically run in any operating system. However, being both open source and more often than not used in a Linux environment, things tend to be tuned better for Linux than Windows.

The client, for their part, is pretty tied to Azure and I believe Microsoft online products in general. They wished to continue hosting with Azure. With some research (and a bit of help from Karl Kedrovsky), I figured out the steps to setup the Linux equivalent of what the currently had.

Because the client ultimately wanted to control the Azure resources, I created the following step-by-step for them to follow.


### After Initial Setup

After the client setup the new stage and production environments I had a few more challenges I ran into. The first was expected. The client hosts their own git repository that was severely unpowered and often got overwhelmed by just cloning the repository down. To resolve that I created a new, free, private repository on Bitbucket and gave them full administrative rights.

The second problem was very unexpected. From the beginning it has appeared that Azure is highly configurable for any setup you wish to use. Unfortunately, in practice this breaks down when you are no longer using a Windows environment. The main issue being with the Apache configuration. It did not let me specify a document root separate from the base deployment directory. Given I was limited on time I was supposed to spend on this I could not spin my wheels for too long.

This might not sound like a big deal, but for those that work with Drupal, you know that the site itself isn't served from the project root, but instead from the "docroot" folder (or some other you specify in the configuration). I also did not want to restructure this Drupal site and preferred to keep it as conventional as possible.

I reached out to several people in TechOps, visiting some in person, and though we tried a number of avenues to change the configuration, it simply would not allow us to change this configuration. I had to work with this default Linux configuration (unless I defined my own Docker image and went down that rabbit hole).

Ultimately, I created a 3 part workaround that allowed me to serve a Drupal site from the Azure App Service Linux instance. First, I had to create a new ".htaccess" in the project root. Then I had to modify the ".htaccess" file in the "docroot" directory. Finally, I had to modify Drupal's "settings.php" file to accommodate this configuration.


