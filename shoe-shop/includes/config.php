<?php
session_start();
ob_start();

// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'shoe_store');
define('DB_USER', 'root');
define('DB_PASS', '');

// Настройки сайта
define('SITE_NAME', 'Магазин обуви');
define('SITE_URL', 'http://localhost/shoe-store');
define('ITEMS_PER_PAGE', 12);

// Автозагрузка классов
spl_autoload_register(function($class) {
    require_once 'classes/' . $class . '.php';
});

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>