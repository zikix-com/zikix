<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

ini_set('date.timezone', 'Asia/Shanghai');

$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->add('Zikix\\LaravelComponent\\Test\\', __DIR__);
