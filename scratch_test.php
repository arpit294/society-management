<?php
$loader = require 'vendor/autoload.php';
var_dump($loader->findFile('Psr\Container\ContainerInterface'));
var_dump(file_exists('vendor/psr/container/src/ContainerInterface.php'));
