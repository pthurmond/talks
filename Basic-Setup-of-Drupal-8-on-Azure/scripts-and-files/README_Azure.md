# Setup Notes

- Had to install mysql `apt-get install mysql-client -y`
- Had to create a custom settings.local.php file for the server. This might not be optimal though for security.
- Had to disable SSL on the DB (can probably re-enable later wiith further config such as generating a certifcate file).
- An article on SSL and other setup stuff can be found here: https://www.howtoforge.com/tutorial/how-to-install-drupal_8-with-apache-and-ssl-on-ubuntu-15-10/
- Once connected, I had to uninstall the devel module: `./vendor/bin/drush pmu devel -y`
- Also have to configure Apache to point to docroot. But it isn't as easy as changing the setting in the Apache config because every time you restart Apache, it loses the changes.

## Configuration Notes
To get this site to work properly on Azure, I had to make the following changes:

* The project root has it's own .htaccess file the points to docroot. This is because the Linux image on Azure does not allow this to be configured. If you edit the Apache config and restart Apache it will wipe out all of your changes. It even wipes out files in the user profile. Don't bother with that stuff.
* I also had to modify the .htaccess withing the docroot directory itself. I enabled the rewrite base setting, which now points to docroot.
* Finally, I had to add a setting to the top of settings.php to get this working.
* The workaround fix was found in this post: [Subdirectory Redirect Fix](https://www.drupal.org/project/drupal/issues/2612160#comment-12538127)

## Build Issues
* Currently Azure runs Node 6.11 and this means builds fail on the server. There are ways to fix this from what I can tell. Though the instructions don't seem to say anything about Linux systems, only Windows. Might or might not work. [Node Versioning](https://github.com/projectkudu/kudu/wiki/Node-versioning) This might also be resolved with some KUDU settings, but I am not sure how to do it. I just know that KUDU provides config
