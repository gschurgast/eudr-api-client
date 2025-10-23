<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests']);

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/.build/.php-cs-fixer.cache')
    ->setRules([
        '@Symfony'                                         => true,
        '@Symfony:risky'                                   => true,

        // Override of the Symfony config
        'binary_operator_spaces'                           => [
            'default'   => 'single_space',
            'operators' => [
                '='  => 'align_single_space_minimal',
                '=>' => 'align_single_space_minimal',
                '+=' => 'align_single_space_minimal',
                '-=' => 'align_single_space_minimal',
            ],
        ],
        'class_attributes_separation'                      => ['elements' => ['method' => 'one', 'property' => 'one']],
        'class_definition'                                 => ['inline_constructor_arguments' => false, 'space_before_parenthesis' => true, 'single_line' => true], // To be PSR12
        'concat_space'                                     => ['spacing' => 'one'], // To be PSR12
        'increment_style'                                  => ['style' => 'post'],
        'no_break_comment'                                 => false, // @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/5080
        'phpdoc_order'                                     => [
            'order' => ['param', 'throws', 'return', 'phpstan-template', 'phpstan-param', 'phpstan-return'],
        ],
        'phpdoc_separation'                                => [
            'groups' => [
                ['var', 'phpstan-var'],
                ['phpstan-template', 'phpstan-extends', 'phpstan-implements', 'phpstan-param', 'phpstan-return'],
                ['phpstan-ignore-next-line'],
            ],
        ],
        'phpdoc_summary'                                   => false,
        'phpdoc_to_comment'                                => ['ignored_tags' => ['throws', 'phpstan-var', 'phpstan-use']],
        'single_line_throw'                                => false,
        'yoda_style'                                       => [
            'always_move_variable' => false,
            'equal'                => false,
            'identical'            => false,
            'less_and_greater'     => false,
        ],

        // Added
        'explicit_string_variable'                         => true,
        'general_phpdoc_annotation_remove'                 => ['annotations' => ['author', 'since', 'package', 'subpackage']],
        'header_comment'                                   => ['header' => ''],
        'no_superfluous_elseif'                            => true,
        'no_useless_else'                                  => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'operator_linebreak'                               => true,
        'phpdoc_no_empty_return'                           => true,
    ])
    ->setFinder($finder);
