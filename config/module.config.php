<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'ZF\\Doctrine\\Audit\\Factory\\ServiceManagerAbstractFactory',
        ),
    ),

    'controllers' => array(
        'abstract_factories' => array(
            'ZF\\Doctrine\\Audit\\Factory\\ControllersAbstractFactory',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
                'zf-doctrine-audit-epoch' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:epoch:create-from-sql',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Epoch',
                            'action' => 'sql',
                        ),
                    ),
                ),
                'zf-doctrine-audit-epoch' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:epoch:create-from-entities',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Epoch',
                            'action' => 'index',
                        ),
                    ),
                ),
                'zf-doctrine-audit-data-fixture-import' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:data-fixture:import',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\DataFixture',
                            'action' => 'import',
                        ),
                    ),
                ),
                'zf-doctrine-audit-schema-tool-update' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:schema-tool:update',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\SchemaTool',
                            'action' => 'update',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
