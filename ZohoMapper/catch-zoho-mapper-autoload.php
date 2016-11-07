<?php

$mapping = array(
    'ZohoMapper\ZohoServiceProvider' => __DIR__. '/zohoServiceProvider.php',
    'ZohoMapper\ZohoMapper' => __DIR__. '/ZohoMapper.php',
);

spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);

/*
require __DIR__ . '/Aws/functions.php';
require __DIR__ . '/GuzzleHttp/functions.php';
require __DIR__ . '/GuzzleHttp/Psr7/functions.php';
require __DIR__ . '/GuzzleHttp/Promise/functions.php';
require __DIR__ . '/JmesPath/JmesPath.php';
*/