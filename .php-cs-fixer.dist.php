<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$directories = array_filter([
    __DIR__ . '/src',
    __DIR__ . '/tests',
], 'is_dir');

$finder = Finder::create()
    ->append([
        __FILE__,
    ]);

if ($directories !== []) {
    $finder->in($directories);
}

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'array_indentation' => true,
        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],
        'blank_line_after_opening_tag' => true,
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'declare_strict_types' => true,
        'line_ending' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'native_function_invocation' => false,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
            ],
        ],
        'no_superfluous_phpdoc_tags' => false,
        'no_trailing_whitespace' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'single_blank_line_at_eof' => true,
        'single_line_empty_body' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'arguments',
                'array_destructuring',
                'arrays',
                'match',
                'parameters',
            ],
        ],
    ])
    ->setFinder($finder);
