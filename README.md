![SpireGG](./publoic/logo-long.svg)

[![Build Status](https://img.shields.io/travis/SpireGG/lolpros-gg.svg?style=flat-square)](https://github.com/SpireGG/lolpros-gg)
[![GitHub tag](https://img.shields.io/github/tag/SpireGG/lolpros-gg.svg?style=flat-square)](https://github.com/SpireGG/lolpros-gg)
[![license](https://img.shields.io/github/license/SpireGG/lolpros-gg.svg?style=flat-square)](https://github.com/SpireGG/lolpros-gg/blob/master/LICENSE)
[![CodeFactor](https://www.codefactor.io/repository/github/spiregg/lolpros-gg/badge)](https://www.codefactor.io/repository/github/spiregg/lolpros-gg)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/f5be324df4a9494fa17ccdc9d0d98ac2)](https://www.codacy.com/manual/SpireGG/lolpros-gg?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=SpireGG/lolpros-gg&amp;utm_campaign=Badge_Grade)
[![Requirements Status](https://requires.io/github/SpireGG/lolpros-gg/requirements.svg?branch=master)](https://requires.io/github/SpireGG/lolpros-gg/requirements/?branch=master)
[![LOLProsGG](https://img.shields.io/twitter/follow/LOLProsGG?label=%40LOLProsGG&style=flat-square)](https://twitter.com/LOLProsGG)


## Installing the project

*Note: You probably want to run this project using our Docker environment. Head over to [docker-gg](https://github.com/SpireGG/docker-gg) in order to install it. This doc assumes that you have installed it and got it running.*   

First thing to do is clone the project and install all related dependencies:

```shell
git clone git@github.com:SpireGG/lolpros-gg.git
composer install
```

You should then setup the environment variables. The `.env` file is configured to be used with our Docker installation, however you will still need to configure your own OAuth tokens and Riot API key.  
Here is a short explanation of each variable that you may need to configure:

**APP_ENV**: Symfony environment, should be set to `prod` when running in production  
**APP_SECRET**: Your secret used for security purposes  
**database_url**: connection to your database   
**MAILER_URL**: connection to your mailer (unused atm)   
**ELASTICA_HOST**: host of your elasticsearch installation  
**ELASTICA_PORT**: port of your elasticsearch installation  
**ELASTICA_PREFIX**: prefix that will be used for ES indexes
**CORS_ALLOW_ORIGIN**: origins allowed for CORS requests  
**JWT_SECRET_KEY**: location of your public jwt encode key  
**JWT_PUBLIC_KEY**: location of your private jwt encode key  
**JWT_PASSPHRASE**: passphrase used by jwt tokens  
**OAUTH_DISCORD_ID**:  (unused atm)   
**OAUTH_DISCORD_SECRET**:  (unused atm)   
**CLOUDINARY_NAME**: cloudinary credentials   
**CLOUDINARY_API_KEY**: cloudinary credentials   
**CLOUDINARY_API_SECRET**: cloudinary credentials  
**RABBITMQ_URL**: connection to RabbitMQ  
**RIOT_API_KEY**: your Riot API key  

If you are using a SQL database, you should then run:

```shell
bin/console doctrine:database:create
bin/console doctrine:migration:migrate
```

This will generate your database and create the required schema. If you are running another database, you should probably use `bin/console doctrine:schema:update --force` instead.

You will then want to generate your jwt keys:

```shell
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

//TODO: Setup hosts


## Developing

### Seeding data

```shell
docker exec -it lpgg-mariadb bash
>  mysql -u spiregg -pggspire spire < /application/mysql/spire.sql
>  exit
docker exec -it lpgg-api bash
> /usr/bin/php var/www/symfony/bin/console f:e:p
>  exit
```

The first command will do a complete load a dump of the database. The second one will populate elasticsearch indexes.

### Start coding

Run this command in order to start up the Dev environment :

```shell
bin/console server:start
```
This will start the symfony dev server, compile and bundle assets with webpack, and watch files for any changes.

You can then go to [localhost:8000][2] in order to access your development website.  
Go to [localhost:8000/doc][3] to access the API documentation.

### Deploying / Publishing
We use [Deployer](https://deployer.org/) to manage deployments. Make sure you tag your commit with the correct version, and simply run

```shell
vendor/bin/dep deploy 
```

 [SemVer][4]  
 [link to tags on this repository][5]  
And again you'd need to tell what the previous code actually does.

## Configuration

Here you should write what are all of the configurations a user can enter when
using the project.

## Tests

Use PHPUnit to run unit tests on the API code. (Refer to the phpunit doc for further informations) 

```shell
bin/phpunit
```

We plan to add tests for JS/Vue components and E2E tests asap.


## Api Reference

If the api is external, link to api documentation. If not describe your api including authentication methods as well as explaining all the endpoints with their required parameters.


## Licensing

All code is licensed under the [MPLv2 License][7].

[2]: http://localhost:8000
[3]: http://localhost:8000/api/doc
[4]: http://semver.org/
[5]: /tags
[6]: https://shoelace.style/
[7]: https://github.com/SpireGG/spire-gg/blob/master/LICENSE
