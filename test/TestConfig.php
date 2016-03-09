<?php
return array(
    'modules' => array(
        'ZF\Doctrine\Audit',

        'DoctrineModule',
        'DoctrineORMModule',

        'ZfcBase',
        'ZfcUser',
        'ZfcUserDoctrineORM',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            '../../../config/autoload/{,*.}{global,local}.php',
            'autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            'module',
            'vendor',
        ),
    ),
);
