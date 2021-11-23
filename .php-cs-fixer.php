<?php
$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1'                  => true,
        '@PSR2'                  => true,
        'single_quote'           => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align'
            ],
         ]
    ])
    ->setFinder($finder)
;
