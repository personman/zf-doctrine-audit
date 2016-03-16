<?php

namespace ZFTest\Doctrine\Audit;

return array(
    'service_manager' => array(
        'invokables' => array(
            'Zend\Authentication\AuthenticationService' => 
                'Zend\Authentication\AuthenticationService',
        ),
    ),

    'zf-doctrine-audit' => array(
        'target_object_manager' => 'doctrine.entitymanager.orm_default',
        'audit_object_manager' => 'doctrine.entitymanager.orm_zf_doctrine_audit',

        'datetime_format' => 'r',
        'paginator_limit' => 999999,

        'authentication_service' => 'Zend\Authentication\AuthenticationService',

        'table_name_prefix' => '',
        'table_name_suffix' => '_audit',

        'entities' => array(
            'ZFTest\Doctrine\Audit\Models\Bootstrap\Album' => array(),
            'ZFTest\Doctrine\Audit\Models\Bootstrap\Performer' => array(),
            'ZFTest\Doctrine\Audit\Models\Bootstrap\Song' => array(),
        ),
    ),

    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'user' => 'test',
                    'password' => 'test',
                    'memory' => true,
                ),
            ),
            'orm_zf_doctrine_audit' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'user' => 'test',
                    'password' => 'test',
                    'memory' => true,
                ),
            ),
        ),

        'driver' => array(
            'ZF_Doctrine_Audit_moduleDriver' => array(
                'class' => 'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver',
            ),

            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\StaticPHPDriver',
                'paths' => array(
                    __DIR__ . '/../AuditTest/Models/Bootstrap',
                ),
            ),

            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Models' => __NAMESPACE__ . '_driver',
                    'ZF\Doctrine\Audit\Entity' => 'ZF_Doctrine_Audit_moduleDriver',
                ),
            ),
        ),
    ),
);

