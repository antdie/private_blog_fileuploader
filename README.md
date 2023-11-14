# Instructions


## Requirement
[Get everything, you can skip IDE and Terminal](https://symfony.com/doc/current/the-fast-track/en/1-tools.html)


## Preparation
Check and set everything correctly with the 2 commands below (skip optional stuff).
```bash
symfony check:requirements
symfony book:check-requirements
```


## Start dev
```bash
# install php dependencies
composer install

# install js dependencies
yarn install

# start php local server
php -S 127.0.0.1:8000 -t public/

# start docker database & mailcatcher in background
docker-compose up -d

# start webpack encore bundler
yarn watch

# create database from migrations
symfony console doctrine:migrations:migrate

# load fake data from fixtures
symfony console doctrine:fixtures:load
```


## Usage
```dotenv
# .env (modify for your need)
SITE_NAME="Site name"
SITE_DESCRIPTION="Site description"
DATE_FORMAT="Y-m-d"
DISCORD_CONTACT="IPSUM#1234"
API_SECRET="Sqnoz10/2^Qu/*work`_B/Zy^qARQa"
API_ALLOWED_IP="127.0.0.1"
POSTS_PER_PAGE=10
FILES_PER_PAGE=20
MAX_SEARCH_RESULTS=30
```
\
Fixtures users (name / pass / role)
```
sa / sa / ROLE_SUPER_ADMIN
a / a / ROLE_ADMIN
u / u / ROLE_USER
```
ROLE_ADMIN : Don't have access to Accounts and Post Categories.


## Api
### [POST] /api/registration
```php
// POST Request :
'secret' => 'Sqnoz10/2^Qu/*work`_B/Zy^qARQa',
'discord_id' => 110883021904703488,
'username' => 'john_doe'

// Responses :
{
        // 200
	"type": "success",
	"username": "john_doe",
	"password": "a45de3"
}
{
	"type": "error",
	"message": "Username already used or you already have an account."
}
// empty 400 if the request isn't valid at all
```
### [POST] /api/lost-password
```php
// POST Request :
'secret' => 'Sqnoz10/2^Qu/*work`_B/Zy^qARQa',
'discord_id' => 110883021904703488

// Responses :
{
        // 200
	"type": "success",
	"password": "a45de3"
}
{
        // 400
	"type": "error",
	"message": "You don't have an account."
}
// empty 400 if the request isn't valid at all
```
### [POST] /api/lost-username
```php
// POST Request :
'secret' => 'Sqnoz10/2^Qu/*work`_B/Zy^qARQa',
'discord_id' => 110883021904703488

// Responses :
{
        // 200
	"type": "success",
	"username": "john_doe",
}
{
        // 400
	"type": "error",
	"message": "You don't have an account."
}
// empty 400 if the request isn't valid at all
```


## Debian Deployment
<details>
  <summary>Debian sudo user group (for fresh install)</summary>

    su -
    usermod -aG sudo $USER
    exit
</details>

---

<details>
  <summary>Install PHP FPM/FCGI (Ondřej Surý repo) with Apache2</summary>

    sudo apt-get install ca-certificates apt-transport-https software-properties-common wget curl lsb-release openssh-server git -y
    curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
    sudo apt update && sudo apt upgrade -y
    sudo apt install apache2 libapache2-mod-fcgid php8.1-fpm php8.1-mbstring php8.1-intl php8.1-pdo-pgsql php8.1-xml php8.1-curl -y
    sudo a2enmod proxy_fcgi setenvif rewrite negotiation
    sudo a2enconf php8.1-fpm
    sudo systemctl restart apache2
</details>

---

<details>
  <summary>Install Composer</summary>

Follow Command-line installation guide -> https://getcomposer.org/download/
</details>

---

<details>
  <summary>Install NodeJS/Yarn</summary>

    curl -sL https://deb.nodesource.com/setup_14.x | sudo bash -
    sudo apt -y install nodejs
    sudo npm install --global yarn
</details>

---

<details>
  <summary>Install Docker</summary>

Follow this install guide -> https://docs.docker.com/engine/install/debian/#install-using-the-repository +
add docker to user group with :

    sudo groupadd docker
    sudo usermod -aG docker $USER
</details>

---

<details>
  <summary>Verify Yarn version & if PHP-FPM is enabled</summary>

    yarn -v # version = 1.* (not 2 & not 3)
    sudo service php8.1-fpm status # Active: active (running)
</details>

---

<details>
  <summary>Get the project from gitlab and deploy</summary>

    ssh-keygen -t ed25519 -C "Server" # add .pub content to gitlab account here -> https://gitlab.com/-/profile/keys
    cd ~ && git clone git@gitlab.com:DIEUD0/private_blog_fileuploader.git
    cd private_blog_fileuploader/ && composer install
    gedit deploy.yaml # AND MODIFY hostname & remote_user according to your server
    php vendor/bin/dep deploy # To prepare the deployed app
    sudo ln -s ~/private_blog_fileuploader/deploy/current /var/www/private_blog_fileuploader # create a Symlink for apache2
</details>

---

<details>
  <summary>Setup Apache2 vhost</summary>

    sudo cp ~/private_blog_fileuploader/virtualhost_apache_php-fpm.conf /etc/apache2/sites-available/private_blog_fileuploader.conf
    sudo a2dissite 000-default
    sudo a2ensite private_blog_fileuploader
    sudo systemctl restart apache2
</details>

---

<details>
  <summary>PHP performance tweaks</summary>

    php -i | grep 'php.ini' (obtained path -> /etc/php/8.1/cli/php.ini)
    sudo gedit obtained_path

    # Delete semicolon at start if present and set correct values
    opcache.memory_consumption=256
    opcache.max_accelerated_files=20000
    realpath_cache_size=4096K
    realpath_cache_ttl=600

    upload_max_filesize = 999M # (Tweak it to your need)
    post_max_size = 1000M # (upload_max_filesize + 1M)

    Save then restart the service with -> sudo systemctl restart php8.1-fpm
</details>

---

<details>
  <summary>Let's encrypt SSL cert (Domain name needed)</summary>

https://www.digitalocean.com/community/tutorials/how-to-secure-apache-with-let-s-encrypt-on-debian-11
</details>

---

You can now visit the website ! Later, when you push new commit (update the site) to gitlab you only have to run the command below (from remote or SSH) to re-deploy the app :
```bash
cd ~/private_blog_fileuploader/ && php vendor/bin/dep deploy
```

## Verify once online :
- Check if 5432 postgresql port access is closed from the outside !!!
- Check long file upload (max_execution_time default 30s / max_input_time default 60s in php.ini)


## Possible upgrade :
- [Setup Gitlab CI for auto deploy](https://deployer.org/docs/7.x/ci-cd#gitlab-cicd)
- Symfony PHPunit tests (to not deploy a broken website after a bad commit)