<?php
return [
    'modules' => [
        'Zend\Router',
        'ZF\Doctrine\Audit',
        'ZFTest\Doctrine\Audit',
        'DoctrineModule',
        'DoctrineORMModule',
        'ZF\Doctrine\DataFixture',
        'ZF\Doctrine\Repository',
    ],
    'module_listener_options' => [
        'config_glob_paths'    => [
            '../../../config/autoload/{,*.}{global,local}.php',
            'autoload/{,*.}{global,local}.php',
        ],
        'module_paths' => [
            'module',
            'vendor',
        ],
    ],
];
