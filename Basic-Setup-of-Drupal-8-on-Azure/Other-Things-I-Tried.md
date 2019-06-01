# **Other Things I Tried**

1. **App Service Configuration:**  I looked in the App Service Configuration. There are a number of articles online that talk about being able to change the document root. However, all of these were ultimately referring to Windows environments and not Linux. The Linux environments do not have this option.

2. **Configuring the path with the Azure CLI:**  As part of the Azure CLI, you can specify the directory the website is served from as separate from where your code is ultimately deployed to. I tried this several times and each time it didn't change anything at all.

3. **Changing the Apache config files:**  The very first thing I tried was to change the Apache config files like I normally would. I SSH'ed into the environment, changed the conf file, and restarted Apache. As soon as Apache restarts, it just wipes out all of my changes. This is probably due to this being an App Service setup. Which means the only part that persists are the files in the directory where everything is deployed to.

4. **Modifying the ".deployment" file:**  There is a project directive you can add to your ".deployment" file that is supposed to be for when the folder you want to deploy is not the root of your repository. I tried this as well and it had no effect.
