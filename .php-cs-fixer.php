<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->exclude('coverage');

return (new PhpCsFixer\Config())
    ->setRules([
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => false,
        'no_multiple_statements_per_line' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_spaces_after_function_name' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'no_useless_else' => true,
        'single_blank_line_at_eof' => true,
    ])
    ->setFinder($finder);