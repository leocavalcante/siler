# Web Servers

By its nature, Siler itself is made to be lightweight and dependent-less.  
This means you can use whatever web-service technology you want.  
Here, you'll find a few example configurations for some well-known web-services.

### Composer-serve script

Siler's default configuration bundled in the `siler/project` template gives a runnable configuration that fits well for development purposes.

You can use it, while being in the root directory of your project \(the folder containing your `composer.json` file\), by running

```text
composer serve
```

or, if you didn't install composer globally,

```text
php composer.phar serve
```

_Note: The `serve` script stored in the `composer.json` file runs `php -S 0.0.0.0:8000 -t .`_

### PHP CLI

The PHP CLI can also be enough for small production servers, like for example a raspberry pi running in your home.

To run your website using the PHP CLI, you can use this simple command \(adapted to your project\):

```text
php -S {ip/domain}[:{port}] -t .
```

#### The `{ip/domain}` block

The `{ip/domain}` block is the interface on which you want your server to listen to.

You have multiple choices:

* `127.0.0.1` will run it on `localhost` and you'll be able to access it by going to `http://127.0.0.1` or `http://localhost`in your browser.
* `192.168.x.x` \(your local IP\) will listen to requests coming from your local IP. You can retrieve it on \*nix systems by running `ifconfig` or `ip addr` and looking for an IP generally starting with `192.168.`.
* `0.0.0.0` will listen to requests coming from _every_ network interface you have on your computer, that'd be `localhost`\(the loopback\), your local IP and more !

Additionally, the PHP CLI supports real domains, which means you can run your website by specifying the domain name of your computer instead of its local IP.

You can retrieve your computer's domain on \*nix systems with the simple command `hostname`.  
It'll return for example `jake-computer`.

You can then use this returned domain as the listening interface: `php -S jake-computer[:{port}] -t .`

#### The `[:{port}]` block

The `[:{port}]` block is the port you want to bind your process to.

> Important: On most \*nix systems, you can't set a port below 1025 without running it as root.  
> The usual ports used are `8080` and `8000`.

_Note: The `:{port}` block is optional, but recommended._

### Apache

#### Subfolder

If you want to make this website available under a sub-folder of your Apache server, you'll need to make sure the `AllowOverride` contains `Options=Multiviews`, like:

```text
<Directory /var/www/html>
    Options +FollowSymLinks +Multiviews
    AllowOverride All
    Order allow,deny
    Allow from all
</Directory>
```

Then, in your project's root folder, you can create a simple `.htaccess` file containing:

```text
# HTTPD mod_rewrite required
RewriteEngine on
# If file/directory's present, serving it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# Else, redirecting the request to the index.php file
RewriteRule . index.php
```

This file will try to see if the file or folder you're trying to access exists in the tree, then if it doesn't exist, it'll redirect the request to the `index.php` entry point in your project.

### Notes

There are a lot of other web services, like NGINX, LigHTTPD or IIS.  
If you want to provide a sample configuration for a server that's not listed here, don't hesitate to fork the repo, do your changes, then submit a pull request.

