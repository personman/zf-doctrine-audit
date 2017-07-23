<?php

namespace ZF\Doctrine\Audit;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'zf-doctrine-repository-plugin' => [
        'aliases' => [
            'audit' => Plugin\AuditPlugin::class,
        ],
        'factories' => [
            Plugin\AuditPlugin::class => InvokableFactory::class,
        ]
    ],
    'service_manager' => [
        'invokables' => [
            'ZF\\Doctrine\\Audit\\Service\\RevisionComment' => 'ZF\\Doctrine\\Audit\\Service\\RevisionComment',
            'ZF\Doctrine\Audit\EventListener\LogRevision' => 'ZF\Doctrine\Audit\EventListener\LogRevision',
            'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver' => 'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver',
            'ZF\Doctrine\Audit\Loader\AuditAutoloader' => 'ZF\Doctrine\Audit\Loader\AuditAutoloader',
        ],
        'initializers' => [
            'ZF\\Doctrine\\Audit\\Persistence\\RevisionCommentInitializer',
        ],
    ],

    'controllers' => [
        'invokables' => [
            'ZF\Doctrine\Audit\Controller\SchemaTool' =>
                'ZF\Doctrine\Audit\Controller\SchemaToolController',
            'ZF\Doctrine\Audit\Controller\DataFixture' =>
                'ZF\Doctrine\Audit\Controller\DataFixtureController',
            'ZF\Doctrine\Audit\Controller\EpochMySQL' =>
                'ZF\Doctrine\Audit\Controller\EpochMySQLController',
            'ZF\Doctrine\Audit\Controller\Field' =>
                'ZF\Doctrine\Audit\Controller\FieldController',
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'zf-doctrine-audit' => __DIR__ . '/../view',
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'zf-doctrine-audit-data-fixture-import' => [
                    'options' => [
                        'route' => 'zf-doctrine-audit:data-fixture:import',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\DataFixture',
                            'action' => 'import',
                        ],
                    ],
                ],
                'zf-doctrine-audit-schema-tool-update' => [
                    'options' => [
                        'route' => 'zf-doctrine-audit:schema-tool:update',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\SchemaTool',
                            'action' => 'update',
                        ],
                    ],
                ],
                'zf-doctrine-audit-epoch-mysql' => [
                    'options' => [
                        'route' => 'zf-doctrine-audit:epoch:import --mysql',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\EpochMySQL',
                            'action' => 'import',
                        ],
                    ],
                ],
                'zf-doctrine-audit-field-deactivate' => [
                    'options' => [
                        'route' => 'zf-doctrine-audit:field:deactivate --entity= --field= [--comment=]',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Field',
                            'action' => 'deactivate',
                        ],
                    ],
                ],
                'zf-doctrine-audit-field-activate' => [
                    'options' => [
                        'route' => 'zf-doctrine-audit:field:activate --entity= --field= [--comment=]',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Field',
                            'action' => 'activate',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
