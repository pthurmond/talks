# Site Project #

This is to cover aspects of doing work on this site both locally and in preparation for a deployment.


## Local Development
I have included Docksal for local development.

* Install Docksal and Docker locally. [Docksal Installation](https://docksal.io/installation)
* Then, in your terminal, go this project's base directory and run `fin up` to get the project loaded up on your machine. Once it is done loading, it will show your local access URL. Usually the format is `https://CURRENT_DIRECTORY_NAME.docksal`.
* Next you will need to download the latest production database to your machine.
* With the exported DB in hand, connect to your Docksal DB using a client of your choice. I highly recommend Sequel Pro (it is free).
* Get the port: Using the port you find when you type in `fin ps`. When you run that command you will see a line that references "mysqld". In the "Ports" column of that same line you will see something like this `0.0.0.0:32797->3306/tcp`. That is the port host to guest OS mapping. The first port number you see, in this case `32797`, is the port number you will specify when connecting to this database.
* Get your IP: The IP address is almost always `192.168.64.100`. However, if you need to check this then run `fin config show`. The `IP` setting near the top will tell you what it is.
* Get your login creds: Username is `user`, Password is `user`. That same config command above will also show you this.
* Get your DB name: The default database name is `default`. However, if it is different then that config command will show you.

#### Other local build notes

* Due to the changes I had to make to get it to work on Azure (which includes adding a .htaccess file to the project root, modifying the .htaccess in docroot to include "RewriteBase /docroot", and adding that new "if" statement to the top of settings.php), I doubt this will load up as is on Docksal anymore (it did before my workarounds).
* The long-term Docksal fix will probably just be to change the Apache config to point at the project root directory. This can be achieved by adding the config files to a subfolder in the ".docksal" directory. Should be able to find examples online easily. If not, VMLY&R has some.


## Pre-Deploy Work

* Currently the deploy script has trouble with compiling the theme assets. This may be due to the node version it is running. Needs to be researched. With that in mind, we need to compile theme assets locally and push to the repository.
* Composer runs fine on the deploy script. No need to commit any of those files.
* I have seen issues with having the devel module enabled. This should be uninstalled unless otherwise needed.
* I believe the only compiled theme assets you need are in the dist/assets directory.


### How to compile the theme for deployment

Here are the steps I believe you need to run through for deployment.

* `cd docroot/themes/site-project`
* `npm install`
* `npm run setup`
* `npm run build:drupal`
* Now commit everything in the "dist/assets" directory.

NOTE: I have noticed an issue with running this via npm as there is a known issue that has not been fixed that sometimes occurs. To get around this I swap "npm" for "yarn" in these commands and the problem is solved. For this I have included a yarn lock file.

I do not believe that we need to run `build:pl` or `build:grav`.

## Deploying Your Code
The deployment is initialized by the `.deployment` file. That kicks off the `deploy.sh` script which handles all deployment operations. Don't forget to build and commit the compiled theme assets.

### Deploy To Stage
Any changes committed and push to the `master` branch are automatically deployed to stage.

### Deploy To Production
Any changes committed and push to the `production` branch are automatically deployed production. Do your work in master first and once you are satisfied with the results on stage just check out the `production` branch and merge the master branch into it. Then commit the merge and push up.

## Azure Setup

More information specific to the Azure setup can be found in `README_Azure.md`.


## Things To Do Next
Suggestions for what should be done next.

* **Security Updates:** Update Drupal core and its modules to fix some major security bugs.

* **Configuration:** Export the configuration from the production database to config files. This is a standard thing to do in Drupal 8 and helps with deploying changes from environment to environment. Currently this isn’t setup on this project.

* **Deployments:** Figure out how to get Azure to use a newer version of Node so that we can build on the server instead of locally.

* **Local Dev Work:** Figure out how to make local development, using Docksal, work with the changes made so that the project would work on Azure. Prior to these changes, I was able to make it run locally using Docksal.

