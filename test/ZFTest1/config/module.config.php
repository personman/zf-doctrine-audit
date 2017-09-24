<?php

return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'params' => array(
                    'charset' => 'utf8',
                ),
            ),
            'orm_zf_doctrine_audit' => array(
                'eventmanager' => 'orm_zf_doctrine_audit',
                'params' => array(
                    'charset' => 'utf8',
                ),
            ),
        ),

        'configuration' => array(
            'orm_zf_doctrine_audit' => array(
                'metadata_cache'    => 'array',
                'query_cache'       => 'array',
                'result_cache'      => 'array',
                'driver'            => 'orm_zf_doctrine_audit',
                'generate_proxies'  => true,
                'proxy_dir'         => 'data/DoctrineORMModule/Proxy',
                'proxy_namespace'   => 'DoctrineORMModule\Proxy',
                'filters'           => array()
            ),
        ),

        'driver' => array(
            'orm_zf_doctrine_audit' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
            ),
        ),

        'entitymanager' => array(
            'orm_zf_doctrine_audit' => array(
                'connection'    => 'orm_zf_doctrine_audit',
                'configuration' => 'orm_zf_doctrine_audit',
            ),
        ),

        'eventmanager' => array(
            'orm_zf_doctrine_audit' => array(),
        ),
    ),
);
