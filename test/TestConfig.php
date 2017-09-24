<?php
return array(
    'modules' => array(
        'Zend\Router',
    'Zend\Mail',
    'Zend\Mvc\Console',
    'Zend\Mvc\I18n',
    'Zend\I18n',
    'Zend\Mvc\Plugin\FilePrg',
    'Zend\Mvc\Plugin\FlashMessenger',
    'Zend\Mvc\Plugin\Identity',
    'Zend\Mvc\Plugin\Prg',
    'Zend\Navigation',
    'Zend\Serializer',
    'Zend\Session',
    'Zend\Cache',
    'Zend\Db',
    'Zend\ServiceManager\Di',
    'Zend\Form',
    'Zend\Filter',
    'Zend\Hydrator',
    'Zend\InputFilter',
    'Zend\Paginator',
        'ZF\Doctrine\Audit',
        'ZFTest\Doctrine\Audit',
        'DoctrineModule',
        'DoctrineORMModule',
        'ZF\Doctrine\DataFixture',
        'ZF\Doctrine\Repository',
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
