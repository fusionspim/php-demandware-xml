<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude(__DIR__ . '/vendor')
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true) // for 'dir_constant'
    ->setRules([
        '@PSR2'                                      => true,
        'array_indentation'                          => true,
        'array_syntax'                               => ['syntax' => 'short'],
        'binary_operator_spaces'                     => ['default' => 'align_single_space'],
        'blank_line_before_statement'                => ['statements' => ['case', 'for', 'foreach', 'if', 'return', 'switch', 'try', 'while']],
        'cast_spaces'                                => ['space' => 'single'],
        'combine_consecutive_issets'                 => true,
        'compact_nullable_typehint'                  => true,
        'concat_space'                               => ['spacing' => 'one'],
        'dir_constant'                               => true,
        'function_typehint_space'                    => true,
        'lowercase_cast'                             => true,
        'lowercase_static_reference'                 => true,
        'method_chaining_indentation'                => true,
        'mb_str_functions'                           => true,
        'no_blank_lines_after_class_opening'         => true,
        'no_break_comment'                           => false,
        'no_extra_blank_lines'                       => ['tokens' => ['extra']],
        'no_mixed_echo_print'                        => ['use' => 'echo'],
        'no_singleline_whitespace_before_semicolons' => true,
        'no_superfluous_phpdoc_tags'                 => true,
        'no_trailing_comma_in_singleline_array'      => true,
        'no_unneeded_control_parentheses'            => ['statements' => ['break', 'clone', 'continue', 'echo_print', 'switch_case', 'yield']], // we love it around 'return'!
        'no_unused_imports'                          => true,
        'no_useless_else'                            => true,
        'no_useless_return'                          => true,
        'no_whitespace_before_comma_in_array'        => true,
        'no_whitespace_in_blank_line'                => true,
        'not_operator_with_successor_space'          => true,
        'ordered_imports'                            => ['importsOrder' => ['class', 'function', 'const'], 'sortAlgorithm' => 'alpha'],
        'return_type_declaration'                    => true,
        'single_import_per_statement'                => false,
        'standardize_not_equals'                     => true,
        'ternary_to_null_coalescing'                 => true,
        'trailing_comma_in_multiline_array'          => true,
        'trim_array_spaces'                          => true,
        'unary_operator_spaces'                      => true,
        'void_return'                                => true,
        'whitespace_after_comma_in_array'            => true,
    ])
    ->setUsingCache(true)
    ->setFinder($finder);
