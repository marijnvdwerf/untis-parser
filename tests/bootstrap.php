<?php

// Set error reporting pretty high
error_reporting(E_ALL | E_STRICT);

$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('UntisParser\\', __DIR__);

define('__BASE__', __DIR__ . '/..');