<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude(__DIR__ . '/vendor')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2'                             => true,
        'array_syntax'                      => ['syntax' => 'short'],
        'single_import_per_statement'       => false,
        'trailing_comma_in_multiline_array' => true,
        'ordered_imports'                   => ['importsOrder' => ['class', 'function', 'const'], 'sortAlgorithm' => 'alpha'],
    ])
    ->setUsingCache(true)
    ->setFinder($finder);
