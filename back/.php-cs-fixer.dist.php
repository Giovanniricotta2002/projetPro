<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

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
    ])
    ->setFinder($finder)
;
