<?php

namespace ZF\Doctrine\Audit;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'doctrine' => [
        'fixture' => [
            'zf-doctrine-audit' => [
                'object_manager' => 'doctrine.entitymanager.orm_zf_doctrine_audit',
                'factories' => [
                    'ZF\Doctrine\Audit\Fixture\FieldStatusFixture'
                        => InvokableFactory::class,
                    'ZF\Doctrine\Audit\Fixture\RevisionTypeFixture'
                        => InvokableFactory::class,
                    'ZF\Doctrine\Audit\Fixture\RevisionEntityFixture'
                        => 'ZF\Doctrine\Audit\Fixture\RevisionEntityFixtureFactory'
                ],
            ],
        ],
    ],
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
            RevisionComment::class => RevisionComment::class
        ],
        'factories' => [
            'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver'
                => 'ZF\Doctrine\Audit\Mapping\Driver\AuditDriverFactory',
            'ZF\Doctrine\Audit\Loader\AuditAutoloader'
                => 'ZF\Doctrine\Audit\Loader\AuditAutoloaderFactory',
            Tools\TriggerTool::class
                => Tools\TriggerToolFactory::class,
            EventListener\PostFlush::class
                => EventListener\PostFlushFactory::class,
        ],
        'initializers' => [
            'ZF\\Doctrine\\Audit\\Persistence\\ObjectManagerInitializer',
        ],
    ],

    'controllers' => [
        'invokables' => [
            'ZF\Doctrine\Audit\Controller\EpochMySQL' =>
                'ZF\Doctrine\Audit\Controller\EpochMySQLController',
            'ZF\Doctrine\Audit\Controller\Field' =>
                'ZF\Doctrine\Audit\Controller\FieldController',
        ],
        'factories' => [
            'ZF\Doctrine\Audit\Controller\SchemaToolController' =>
                'ZF\Doctrine\Audit\Controller\SchemaToolControllerFactory',
            'ZF\Doctrine\Audit\Controller\TriggerToolController' =>
                'ZF\Doctrine\Audit\Controller\TriggerToolControllerFactory',
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
                'zf-doctrine-audit-schema-tool-update' => [
                    'options' => [
                        'route' => 'audit:schema-tool:update',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\SchemaToolController',
                            'action' => 'update',
                        ],
                    ],
                ],
                'zf-doctrine-audit-trigger-tool-create' => [
                    'options' => [
                        'route' => 'audit:trigger-tool:create',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\TriggerToolController',
                            'action' => 'create',
                        ],
                    ],
                ],
                'zf-doctrine-audit-epoch-mysql' => [
                    'options' => [
                        'route' => 'audit:epoch:import --mysql',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\EpochMySQL',
                            'action' => 'import',
                        ],
                    ],
                ],
                'zf-doctrine-audit-field-deactivate' => [
                    'options' => [
                        'route' => 'audit:field:deactivate --entity= --field= [--comment=]',
                        'defaults' => [
                            'controller' => 'ZF\\Doctrine\\Audit\\Controller\\Field',
                            'action' => 'deactivate',
                        ],
                    ],
                ],
                'zf-doctrine-audit-field-activate' => [
                    'options' => [
                        'route' => 'audit:field:activate --entity= --field= [--comment=]',
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
