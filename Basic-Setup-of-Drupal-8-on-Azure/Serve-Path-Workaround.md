# Serve From Path Issue
During setup, I ran into issues with Azure trying to serve the site from the repository root directory instead of the "docroot" directory. It did not allow me to change the serve from path, despite trying a number of different things.


### **What I did to fix the serve from directory Issues**

1. Created a ".htaccess" file in the project root with the following in it.

```apacheconfig
<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule ^$ docroot/$1 [L]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ docroot/$1 [L]
</IfModule>
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
