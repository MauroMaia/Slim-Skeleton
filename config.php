<?php

defined('BASE_PATH')  or define('BASE_PATH', '/slim-skeleton');

// db
defined('DATABASE_HOST')  or define('DATABASE_HOST', '127.0.0.1');
defined('DATABASE_NAME')  or define('DATABASE_NAME', 'slim');
defined('DATABASE_USER')  or define('DATABASE_USER', 'root');
defined('DATABASE_PASSWORD')  or define('DATABASE_PASSWORD', 'password');
defined('DATABASE_PORT')  or define('DATABASE_PORT', 3306);

// JWT
defined('ALGORITHM') or define('ALGORITHM', 'HS256');
defined('TYPE') or define('TYPE', 'JWT');
defined('ISSUER') or define('ISSUER', '192.168.2.48');
defined('AUDIENCE') or define('AUDIENCE', [ISSUER]);
defined('SECRET') or define('SECRET', 'very_secure_password_to_be_store_elsewhere');
defined('EXPIRATION_TIME_SECONDS') or define('EXPIRATION_TIME_SECONDS', 2 * 60 * 60); // 2h
defined('NOT_BEFORE_SECONDS') or define('NOT_BEFORE_SECONDS', 0);


function rand_string( $length ) {

    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars),0,$length);

}
