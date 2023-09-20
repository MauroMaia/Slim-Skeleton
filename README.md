# Slim Framework 4 Skeleton Application

This project is based on [Slim-Skeleton](https://github.com/slimphp/Slim-Skeleton). Thank you for your effort.

Use this skeleton application to quickly setup and start working on a new Slim Framework 4 application.
This application uses the latest Slim 4 with Slim PSR-7 implementation and PHP-DI container implementation.
It also uses the Monolog logger.

This skeleton application was built for Composer.
This makes setting up a new Slim Framework application quick and easy.

You will find all the bases to create your own web site including mail, mysql/PDO database access, login pages and user management.

This project also uses CSRF token to improve security.

## Install the Application

Run this command from the directory in which you want to install your new Slim Framework application. You will require PHP 7.4 or newer.

```bash
composer create-project slim/slim-skeleton [my-app-name]
```

Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

* Point your virtual host document root to your new application's `public/` directory.
* Ensure `logs/` is web writable.

To run the application in development, you can run these commands 

```bash
cd [my-app-name]
composer start
```

Or you can use `docker-compose` to run the app with `docker`, so you can run these commands:
```bash
cd [my-app-name]
docker-compose up -d
```
After that, open `http://localhost:8080` in your browser.


Dont forget to:
```
npm install -g grunt-cli
grunt css   # to create min.css
grunt js    # to create min.js
grunt # just does js + css
```

That's it! Now go build something cool.
