<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'charset' => 'utf8',
                ],
            ],
            'orm_zf_doctrine_audit' => [
                'eventmanager' => 'orm_zf_doctrine_audit',
                'params' => [
                    'charset' => 'utf8',
                ],
            ],
        ],

        'configuration' => [
            'orm_zf_doctrine_audit' => [
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'driver'            => 'orm_zf_doctrine_audit',
                'generate_proxies'  => true,
                'proxy_dir'         => 'data/DoctrineORMModule/Proxy',
                'proxy_namespace'   => 'DoctrineORMModule\Proxy',
                'filters'           => []
            ],
        ],

        'driver' => [
            'orm_zf_doctrine_audit' => [
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
            ],
        ],

        'entitymanager' => [
            'orm_zf_doctrine_audit' => [
                'connection'    => 'orm_zf_doctrine_audit',
                'configuration' => 'orm_zf_doctrine_audit',
            ],
        ],

        'eventmanager' => [
            'orm_zf_doctrine_audit' => [],
        ],
    ],
];
