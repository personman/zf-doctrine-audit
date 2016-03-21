<?php

return array(
    'service_manager' => array(
        'abstract_factories' => array(
            'ZF\\Doctrine\\Audit\\Factory\\ServiceManagerAbstractFactory',
        ),
        'invokables' => array(
            'ZF\\Doctrine\\Audit\\Service\\RevisionComment' => 'ZF\\Doctrine\\Audit\\Service\\RevisionComment',
        ),
        'initializers' => array(
            'ZF\\Doctrine\\Audit\\Persistence\\RevisionCommentInitializer',
        ),
    ),

    'controllers' => array(
        'abstract_factories' => array(
            'ZF\\Doctrine\\Audit\\Factory\\ControllersAbstractFactory',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'zf-doctrine-audit' => __DIR__ . '/../view',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
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
                'zf-doctrine-audit-epoch-mysql' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:epoch:import --mysql',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\EpochMySQL',
                            'action' => 'import',
                        ),
                    ),
                ),
                'zf-doctrine-audit-field-deactivate' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:field:deactivate --entity= --field= [--comment=]',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Field',
                            'action' => 'deactivate',
                        ),
                    ),
                ),
                'zf-doctrine-audit-field-activate' => array(
                    'options' => array(
                        'route' => 'zf-doctrine-audit:field:activate --entity= --field= [--comment=]',
                        'defaults' => array(
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Field',
                            'action' => 'activate',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
