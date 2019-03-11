<?php

if (ENVIRONMENT == 'development'):
    define('HOST', 'localhost');
    define('USER', 'root');
    define('PASS', '');
    define('DBSA', 'loja_virtual');
    define('BASE_URL', 'http://localhost/lojaphp/');
elseif (ENVIRONMENT == 'production'):
    define('HOST', '');
    define('USER', '');
    define('PASS', '');
    define('DBSA', '');
    define('DBSA', '');
    define('BASE_URL', 'https://jucielima.com/admin/');
endif;

define('TEMPLATE', 'template/index');
define('SITENAME', 'Loja do Juciê');