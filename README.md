# Gerald's Produce Stand
Live Site: https://ianpeoples.dev/geralds-produce-stand/

A small web application that tracks the stock of various produce. Built using MySQL/Mariadb, Python 3, and PHP 7.

## Table of Contents:
[Top](#geralds-produce-stand)  
[Commentary](#commentary)  
[Installation](#installation)  
[Running Locally](#running-locally)  
[Uninstallation](#uninstallation)  
[Linting](#linting)

### Commentary

I wrote this application by hand. All the HTML/CSS/PHP is my own. I thought that might be quicker since it is such a (seemingly) simple application, and also easier to distribute. It turns out web frameworks exist for a reason. That being said, I learned a lot and really enjoyed making this. There were a lot of small tricks I learned (see the comments) that I will carry into future projects (and update old ones using them!).  

If I were to continue this project, I would seriously consider migrating to an established web framework like Django or React. In addition, I think the styling could be updated to look more "modern". Also there are a few violations of the DRY principle that could easily be fixed (the page headers and navbars).  

Overall, I am very proud of the work and effort I have put forth here, and I hope it shows!

### Installation

I tested the installation using a Debian server running Apache, other Linux distros and webservers should have a similar procedure. I have tried to streamline this as much as possible, and most of the first configuration steps will already be completed on an already functioning webserver.

Make sure the necessary OS packages are installed:
```
apt-get install php mariadb-server php-mysql python3 python3-pip libmariadb3 libmariadb-dev
```
At this point MariaDB recommends you run:
```
mysql_secure_installation
```
To better secure your database.

Install the necessary python packages:
```
pip3 install mariadb
```

If running Apache verify that the `.htaccess` files will be respected. To do this, open `/etc/apache2/apache2.conf` and verify that the `/var/www/` entry looks something like this:
```
<Directory /var/www/>
        Options Indexes FollowSymLinks
        AllowOverride all
        Require all granted
</Directory>
```
Specifically `AllowOverride` should be set to `all`. If you edited this `apache2.conf` then you will need to restart the Apache service.
```
service apache2 restart
```
This will ensure that the `.htaccess` files in the repository will be used by the Apache webserver and that no one will be able to access the sensitive configuration files.

Now navigate to the webserver folder, usually something like:
```
cd /var/www/html/
```
And clone the repository:
```
git clone https://github.com/idpeoples/geralds-produce-stand.git
```

Now we need to edit the configuration and installation files. Open `configuration/configuration.json` and `installation/installlation.json` in your favorite text editor. In `configuration.json` edit the values to your heart's content. Make sure to change the `"password"` value to something other than `"example"` (but leave the quotes). Make sure the username, password and database name match in `install.sql`.

After the configuration and installation files have been modified, run the install scripts:
```
cd installation
mysql -u root -p -e "source install.sql"
python3 install.py
```
These scripts will create the database, database user, and database table that the application uses.

If you want to use the email feature, make sure your webserver has a working mail server and PHP is configured to use it.

Now the application should be set up! Check `[your url]/geralds-produce-stand`.

### Running Locally

You can also skip the Apache steps and run a local server just for testing. Navigate to the project directory, then run:
```
php -S localhost:8000
```
Then point a webbrowser to localhost:8000 and view the page.

### Uninstallation

Simply run `uninstall.sql` in the installation directory (make sure the information in it matches the other configuration files). That will delete the database and user from your server. From there you can delete the repository and that should do it!

### Linting

I have included files for linting with Prettier.io, as well Stylelint. Use them if you'd like!
