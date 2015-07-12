<?php
require_once __DIR__ . '/vendor/autoload.php';

require 'tests/products.php'; // products, sets and bundles
require 'tests/categories.php'; // categories and assignments
require 'tests/variants.php';

foreach (['assignments.xml', 'categories.xml', 'products.xml', 'variants.xml'] as $file) {
    $exampleHash = sha1_file(__DIR__ . '/examples/' . $file);
    $testHash    = sha1_file(__DIR__ . '/out/' . $file);

    echo $file . ($exampleHash === $testHash ? ' passed' : ' failed') . PHP_EOL;
}
