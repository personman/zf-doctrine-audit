<?php

namespace ZFTest\Doctrine\Audit;

$host = getenv('DB_HOST');
if ($host !== false) {
    $ormParams['host'] = $host;
} else {
    $host = 'mysql';
}

return [
    'service_manager' => [
        'invokables' => [
            'Zend\Authentication\AuthenticationService' =>
                'Zend\Authentication\AuthenticationService',
        ],
    ],

    'zf-doctrine-audit' => [
        'target_object_manager' => 'doctrine.entitymanager.orm_default',
        'audit_object_manager' => 'doctrine.entitymanager.orm_zf_doctrine_audit',

        'paginator_limit' => 999999,

        'authentication_service' => 'Zend\Authentication\AuthenticationService',

        'table_name_prefix' => '',
        'table_name_suffix' => '_audit',

        'entities' => [
            'ZFTest\Doctrine\Audit\Entity\Artist' => [],
            'ZFTest\Doctrine\Audit\Entity\Album' => [],
        ],
        'joinEntities' => [
            'ZFTest\Doctrine\Audit\Entity\UserToAlbum' => [
                'ownerEntity' => 'ZFTest\Doctrine\Audit\Entity\Album',
                'tableName' => 'UserToAlbum',
            ],
        ],
    ],

    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => [
                    'user'  => 'root',
                    'password'  => '',
                    'host'  => $host,
                    'dbname'  => 'test',
                    'charset' => 'utf8',
                    'collate' => "utf8_unicode_ci",
                ],
            ],
            'orm_zf_doctrine_audit' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => [
                    'user'  => 'root',
                    'password'  => '',
                    'host'  => $host,
                    'dbname'  => 'audit',
                    'charset' => 'utf8',
                    'collate' => "utf8_unicode_ci",
                ],
            ],
        ],

        'driver' => [
            'zftest_driver' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
                'paths' => [
                    0 => __DIR__ . '/../ZFTest/config/orm',
                ],
            ],
            'orm_default' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
                'drivers' => [
                    'ZFTest\\Doctrine\\Audit\\Entity' => 'zftest_driver',
                ],
            ],
        ],
    ],
];
