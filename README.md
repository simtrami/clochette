# Clochette

## A Symfony web app for bar-oriented point-of-sells

### Introduction

The project came from a need for the student's bar of the campus of the French higher education schools Télécom SudParis (TSP) and the Institut Mines-Télécom Business School (IMT-BS) to manage sells, client accounts, stock and treasury more efficiently.
It began as a Development Project for 4 first year students from TSP and was continues by 2 of them. Now it is mainly carried by me (Simtrami) and I am very open to any sort of help.

The app was deployed into production in the beginning of October 2018 while still in active development. The source code had and will always be publicly available, however, as for now, it suffers from the fact it is developed specifically for the bar (called AbsINThe ; INT is from the schools' old name) and therefore it is virtually impossible to use it for any other bar without rewriting a good chunk of the code.
We are aware of that and project to rewrite quasi-entirely this code when the app reaches a stable point and the vast majority of its main features are added.

This is basically our first Symfony project and if you dig into the code history a little you'll see that it did not start well.
With a lot of practice and kilometers of documentation read, we managed to make it a little more efficient and "clean", although we are for sure not at all doing things as we should.
For short, it would be an understatement to say that we need, at least, some advice.

This project is under [GPLv3 license](https://www.gnu.org/licenses/gpl-3.0.en.html), therefore its code can be used, copied, modified and published as long as it remains under this very same license.

### Pull requests? Please do!

As said in introduction, this app is still under active development.
It shall reach a stable point when this sentence is striked.
However, we implemented the [Git branching model presented by Vincent Driessen](https://nvie.com/posts/a-successful-git-branching-model/) so that it makes it easier for potential contributors to watch and understand the way we develop and offer pull requests comfortably.

As you can also see, we're using Github Projects, milestones and issues for advice, feature requests and bug reporting.
Feel free to open your own issue, it shall be answered very shortly after.

If you are interested in actively contributing to the project and demand more information about it, you can contact us at [clochette@clochette.beer](mailto:clochette@clochette.beer), we would be glad to welcome you and answer any of your questions!

### How it works

This app is deployed on a dedicated web server on which is also installed an OpenVPN server.
It is through this VPN that we connect the app to the receipt printer and the cash-drawer.
Indeed, these two are plugged in via USB to a client PC at the bar which connects to the VPN at startup and starts listening wth netcat to the specified port in app/config/parameters.yml and transmits everything the server sends to the receipt printer (which controls the cash-drawer).
I shall add more info such as the script and the printer and drawer models in the future.

Other than that, once deployed on a web server, either locally or online, anyone working at the bar can connect to the app (via the PC or with their smartphones if deployed online) and start using it for sells, stock management, treasury, etc.

Documentation for using the app shall be added progressively as things can change very quickly.

### How to develop

Translated extract from documentation.md for setting up the development environment:

#### Requirements

- PHP v7.1+
- Composer
- MySQL/MariaDB (latest)
- Either Windows 10 or Debian based GNU/Linux distro

If you already have all the requirements, jump ahead to [Clone the project and prepare your database](#clone-the-project-and-prepare-your-database).

#### PHP

##### From Windows

Download the latest version of PHP in **.zip (x64 Thread Safe)** on [PHP for Windows website](https://windows.php.net/download/).
Then extract the archive content at **C:\php\*** that needs to be added to PATH:

Open a terminal (`Windows+R`>`cmd`>`OK`)
```sh
set PATH=%PATH%;C:\php
```
Verify that it worked by opening another terminal or Powershell and typing `php -v`. It should return the version of PHP you just installed. Otherwise, try disconnecting and reconnecting the active user session.

###### From Ubuntu

In order to be sure to always have the latest version of PHP installed, add the PPA repository of ondrej.

```sh
sudo apt update
sudo apt install software-properties-common python3-software-properties -y
sudo LC_ALL=fr_FR.UTF-8 add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php php-mysql php-mbstring php-zip php-xml php-curl -y
```
As for Windows, verify the installation by typing `php -v`.

##### Composer

###### From Windows

Got to the [Composer website](https://getcomposer.org/download/) and download the Windows Installer at the top of the page.
Then execute and install Composer by taking care of indicating the path for the recently installed php (`C:\php\`).

A message offering to modify the PHP configuration may appear, accept it.

At the end of the process, go to **C:\php\*** and modify the **php.ini** file by uncommenting `extension=pdo_mysql`, `extension=curl`, `extension=mbstring`, `extension=xml` and `extension=zip`.

Again, verify the installation by typing `composer -v` in the terminal.

If it does not work, add manually Composer in the PATH:
```sh
set PATH=%PATH%;C:\ProgramData\ComposerSetup\bin
```

###### From Ubuntu

For installing Composer in the working directory, follow the procedure on the [Composer website](https://getcomposer.org/download/).

Otherwise, do `sudo apt install composer`.

Then verify the installation by typing `composer -v` or if installed locally `php compser.phar -v`.

##### MariaDB

###### From Windows

Go to the [MariaDB website](https://downloads.mariadb.org/), select the latest version and download the **winx64.msi** installer.
During installation, check "Use UTF8 as default server's character set".

Verify the installation in the terminal:
```sh
mysql -u root -p
```
If the command in not found, tru adding `C:\Program Files\MariaDB [version number]\bin` to PATH.
A session reload can be necessary.

###### From Ubuntu

Install MariaDB via apt:
```sh
sudo apt update
sudo apt install mariadb-server
```
Do indicate a root password during installation.

Then do and follow the guidelines:
```bash
mysql_secure_installation
```

Then finally, verify by connecting to the database manager:
```sh
mysql -u root -p
```

#### Clone the project and prepare your database

```sh
git clone -b develop https://github.com/simtrami/clochette.git
cd clochette
composer install
```
And indicate your parameters for the project (db user, db password, mailer address, etc.) Don't forget to add a secret:

At this line while `composer install` is being executed `secret (ThisTokenIsNotSoSecretChangeIt):`, open a new terminal and enter
```sh
cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 40 | head -n 1
```
Then copy and past the resultin string into the first terminal window.

If everything went well, you are now able to create the database schema.

From the project root:
```sh
php bin/consoler doctrine:schema:create
```

Then you have to set up the SUPER_ADMIN user for the database. First, generate a password hash:
```sh
php bin/console security:encode-password
```
Type in you password and copy the returned bcrypt hash.

Connect to the database and insert a new user.
```sql
USE <db_name>;
INSERT INTO app_user VALUES(DEFAULT,"admin","<password_hash>","ROLE_SUPER_ADMIN",DEFAULT);
```

You should now see the user in the database:
```sql
SELECT * FROM app_user;
```

##### Try it out!

Now you can finally run the integrated Symfony PHP server by running
```sh
php bin/console server:run
```
And see the site by clicking on the returned link.

