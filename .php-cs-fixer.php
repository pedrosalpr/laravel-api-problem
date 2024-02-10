<?php

$config = new PhpCsFixer\Config();
return  $config->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => ['statements' => []],
        'compact_nullable_type_declaration' => true,
        'concat_space' => ['spacing' => 'one'],
        'control_structure_braces' => true,
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'braces_position' => true,
        'declare_equal_normalize' => ['space' => 'none'],
        'declare_parentheses' => true,
        'declare_strict_types' => true,
        'type_declaration_spaces' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'new_with_parentheses' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'case',
                'continue',
                'curly_brace_block',
                'default',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'switch',
                'throw',
                'use'
            ]
        ],
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiple_statements_per_line' => true,
        'no_trailing_comma_in_singleline' => [ 'elements' => [
            'arguments',
            'array_destructuring',
            'array',
            'group_import',
        ]],
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'php_unit_internal_class' => [],
        'php_unit_test_class_requires_covers' => false,
        'phpdoc_summary' => false,
        'return_assignment' => false,
        'return_type_declaration' => ['space_before' => 'none'],
        'single_import_per_statement' => false,
        'single_space_around_construct' => true,
        'single_trait_insert_per_statement' => false,
        'statement_indentation' => true,
        'trailing_comma_in_multiline' => [],
        'no_superfluous_phpdoc_tags' => false,
        'yoda_style' => [
            'always_move_variable' => false,
            'equal' => false,
            'identical' => false,
            'less_and_greater' => null
        ]
    ])->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['bootstrap', 'storage', 'vendor'])
            ->notName(['_*.php'])
            ->in(__DIR__)
    );
