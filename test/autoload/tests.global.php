<?php

namespace ZFTest\Doctrine\Audit;

return array(
    'zf-doctrine-audit' => array(
        'datetimeFormat' => 'r',
        'paginatorLimit' => 999999,

        'userEntityClassName' => 'ZfcUserDoctrineORM\Entity\User',
        'authenticationService' => 'zfcuser_auth_service',

        'tableNamePrefix' => '',
        'tableNameSuffix' => '_audit',
        'revisionTableName' => 'Revision',
        'revisionEntityTableName' => 'RevisionEntity',

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

