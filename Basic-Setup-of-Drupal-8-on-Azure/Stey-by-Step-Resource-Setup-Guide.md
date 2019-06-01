# Step-By-Step Resource Setup Guide

### **Before you begin**

- The pricing tiers selected below are appropriate for the stage environment.
- For production we will want to give the site more resources for the "App Service Plan" and the database.
- In time you may adjust these plans for production to suite your needs.

### **First create your "App Service Plan"**

1. In the Azure Portal, click "Create a resource"
2. Type in "App Service Plan" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Put in the plan name, select your subscription, name the resource group you want to use for this (I suggest a new one just for this).
5. Select "Linux" as your operating system.
6. Select pricing tier "B1 Basic" and click "Create"

### **Then create your web app**

1. In the Azure Portal, click "Create a resource"
2. Type in "Web" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Enter the App Name, for Resource Group select "Use existing" and select the same one as your App Service Plan.
5. Select the existing App Service Plan.
6. For "OS" select "Linux" and for "Runtime Stack" select "PHP 7.2".
7. Click "Create".

### **Then your database**

1. In the Azure Portal, click "Create a resource"
2. Type in "Azure Database for MySQL" and select that option when it comes up in the list.
3. Click "Create" at the bottom.
4. Enter server name of your choice, select the same resource group as above.
5. Enter a custom username and password.
6. Change pricing tier to "Basic, 1 vCore(s), 50GB" (should be fine for now).
7. Click "Create".