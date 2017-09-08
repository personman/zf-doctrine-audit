<?php

namespace ZF\Doctrine\Audit;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'doctrine' => [
        'fixture' => [
            'zf-doctrine-audit' => [
                'object_manager' => 'doctrine.entitymanager.orm_zf_doctrine_audit',
                'factories' => [
                    Fixture\RevisionTypeFixture::class
                        => InvokableFactory::class,
                    Fixture\RevisionEntityFixture::class
                        => Fixture\RevisionEntityFixtureFactory::class,
                    Fixture\RevisionJoinEntityFixture::class
                        => Fixture\RevisionJoinEntityFixtureFactory::class,
                ],
            ],
        ],
    ],
    'zf-doctrine-repository-plugin' => [
        'aliases' => [
            'audit' => Plugin\AuditPlugin::class,
        ],
        'factories' => [
            Plugin\AuditPlugin::class
                => InvokableFactory::class,
        ]
    ],
    'service_manager' => [
        'invokables' => [
            RevisionComment::class
                => RevisionComment::class
        ],
        'factories' => [
            AuditOptions::class
                => AuditOptionsFactory::class,
            Loader\EntityAutoloader::class
                => Loader\EntityAutoloaderFactory::class,
            Loader\JoinEntityAutoloader::class
                => Loader\JoinEntityAutoloaderFactory::class,
            Mapping\Driver\MergedDriver::class
                => Mapping\Driver\MergedDriverFactory::class,
            Mapping\Driver\EntityDriver::class
                => Mapping\Driver\EntityDriverFactory::class,
            Mapping\Driver\JoinEntityDriver::class
                => Mapping\Driver\JoinEntityDriverFactory::class,
            Tools\TriggerTool::class
                => Tools\TriggerToolFactory::class,
            EventListener\PostFlush::class
                => EventListener\PostFlushFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\SchemaToolController::class =>
                Controller\SchemaToolControllerFactory::class,
            Controller\TriggerToolController::class =>
                Controller\TriggerToolControllerFactory::class,
            Controller\Epoch\MySQLController::class =>
                Controller\Epoch\MySQLControllerFactory::class,
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
                            'controller' => Controller\SchemaToolController::class,
                            'action' => 'update',
                        ],
                    ],
                ],
                'zf-doctrine-audit-trigger-tool-create' => [
                    'options' => [
                        'route' => 'audit:trigger-tool:create --mysql',
                        'defaults' => [
                            'controller' => Controller\TriggerToolController::class,
                            'action' => 'create',
                        ],
                    ],
                ],
                'zf-doctrine-audit-epoch-mysql' => [
                    'options' => [
                        'route' => 'audit:epoch:import --mysql',
                        'defaults' => [
                            'controller' => Controller\Epoch\MySQLController::class,
                            'action' => 'import',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
