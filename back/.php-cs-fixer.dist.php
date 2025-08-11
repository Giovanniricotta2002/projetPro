<?php

use PhpCsFixer\{Config, Finder};

$finder = (new Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor'])
;

return (new Config())
    ->setRiskyAllowed(false) // Évite les règles "risquées"
    ->setIndent('    ') // Indentation en 4 espaces
    ->setLineEnding("\n") // Fin de ligne UNIX
    ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
        'phpdoc_to_comment' => true,
        'explicit_string_variable' => true,
        'concat_space' => ['spacing' => 'one'],
        'array_syntax' => ['syntax' => 'short'],
        'no_whitespace_before_comma_in_array' => ['after_heredoc' => false],
        'group_import' => ['group_types' => ['classy']],
        'single_import_per_statement' => false,
        'list_syntax' => ['syntax' => 'short'],
        // 'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
    ])
    ->setFinder($finder)
;
