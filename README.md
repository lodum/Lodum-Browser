# LODUM Browser

A web-based browser for the **Linked Data University Muenster (LODUM)** (see [data.uni-muenster.de](http://data.uni-muenster.de)). 
The browser is build upon the [https://github.com/hxl-team/HXL-Browser](HXL Browser).

To have the URIs resolved, the server needs to redirect all requests that have a certain fragment in the URI to the PHP script. Something like this should do the job:

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule> 