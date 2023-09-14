<?php

use Monolog\Level;

defined('BASE_PATH')        or define('BASE_PATH', '/slim-skeleton');
defined('APP_NAME')         or define('APP_NAME', 'slim-skeleton');
defined('APP_DESCRIPTION')  or define('APP_DESCRIPTION', 'slim-skeleton');


// SLIM
// Should be set to false in production
defined('DISPLAY_ERRORS')   or define('DISPLAY_ERRORS', true);

// logger
defined('LOGGER_REGISTER_ERRORS')           or define('LOGGER_REGISTER_ERRORS', true);
defined('LOGGER_REGISTER_ERRORS_DETAILS')   or define('LOGGER_REGISTER_ERRORS_DETAILS', true);
defined('LOGGER_INTERNAL_CONFIGS')          or define('LOGGER_INTERNAL_CONFIGS', [
    'name' => str_replace(' ','',APP_NAME),
    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/logs/app.log',
    'level' => Level::Debug,
]);

// db
defined('DATABASE_HOST')        or define('DATABASE_HOST', '127.0.0.1');
defined('DATABASE_NAME')        or define('DATABASE_NAME', 'slim');
defined('DATABASE_USER')        or define('DATABASE_USER', 'root');
defined('DATABASE_PASSWORD')    or define('DATABASE_PASSWORD', 'password');
defined('DATABASE_PORT')        or define('DATABASE_PORT', 3306);

// JWT
defined('ALGORITHM')                or define('ALGORITHM', 'HS256');
defined('TYPE')                     or define('TYPE', 'JWT');
defined('ISSUER')                   or define('ISSUER', '127.0.0.1');
defined('AUDIENCE')                 or define('AUDIENCE', [ISSUER]);
defined('SECRET')                   or define('SECRET', 'very_secure_password_to_be_store_elsewhere');
defined('EXPIRATION_TIME_SECONDS')  or define('EXPIRATION_TIME_SECONDS', 2 * 60 * 60); // 2h
defined('NOT_BEFORE_SECONDS')       or define('NOT_BEFORE_SECONDS', 0);

// PHP MAILER
defined('MAIL_FROM')            or define('MAIL_FROM', '');
defined('MAIL_FROM_PASSWORD')   or define('MAIL_FROM_PASSWORD', '');
defined('MAIL_SERVER')          or define('MAIL_SERVER', '');
defined('MAIL_PORT')            or define('MAIL_PORT', 587);


function rand_string( $length ): string
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);

}
