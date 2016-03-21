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
        ),
    ),

    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'memory' => true,
                ),
            ),
            'orm_zf_doctrine_audit' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                'params' => array(
                    'memory' => true,
                ),
            ),
        ),
    ),
);

