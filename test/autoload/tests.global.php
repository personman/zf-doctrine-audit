<?php

namespace ZFTest\Doctrine\Audit;

$host = getenv('DB_HOST');
if ($host !== false) {
    $ormParams['host'] = $host;
} else {
    $host = 'mysql';
}

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
            'ZFTest\Doctrine\Audit\Entity\Artist' => [],
            'ZFTest\Doctrine\Audit\Entity\Album' => [],
        ),
        'joinEntities' => [],
    ),

    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'user'  => 'root',
                    'password'  => '',
                    'host'  => $host,
                    'dbname'  => 'test',
                    'charset' => 'utf8',
                    'collate' => "utf8_unicode_ci",
                ),
            ),
            'orm_zf_doctrine_audit' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'user'  => 'root',
                    'password'  => '',
                    'host'  => $host,
                    'dbname'  => 'audit',
                    'charset' => 'utf8',
                    'collate' => "utf8_unicode_ci",
                ),
            ),
        ),

        'driver' => array(
            'zftest_driver' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\XmlDriver',
                'paths' => array(
                    0 => __DIR__ . '/../ZFTest/config/orm',
                ),
            ),
            'orm_default' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
                'drivers' => array(
                    'ZFTest\\Doctrine\\Audit\\Entity' => 'zftest_driver',
                ),
            ),
        ),
    ),
);

