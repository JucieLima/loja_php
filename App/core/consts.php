<?php

if (ENVIRONMENT == 'development'):
    define('HOST', 'localhost');
    define('USER', 'root');
    define('PASS', '');
    define('DBSA', 'loja_virtual');
    define('BASE_URL', 'http://localhost/lojaphp/');
elseif (ENVIRONMENT == 'production'):
    define('HOST', 'localhost');
    define('USER', 'u254290359_loja');
    define('PASS', 'XEiHIQWLBd0v');
    define('DBSA', 'u254290359_loja');
    define('BASE_URL', 'https://loja.jucielima.com/');
endif;

define('TEMPLATE', 'template/index');
define('SITENAME', 'Loja do Juciê');
define('EMAIL', 'juciegeraldo@hotmail.com');