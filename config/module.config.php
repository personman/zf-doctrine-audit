<?php

return array(
    'service_manager' => array(
        'invokables' => array(
            'ZF\Doctrine\Audit\EventListener\LogRevision' => 'ZF\Doctrine\Audit\EventListener\LogRevision',
            'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver' => 'ZF\Doctrine\Audit\Mapping\Driver\AuditDriver',
            'ZF\Doctrine\Audit\Service\AuditService' => 'ZF\Doctrine\Audit\Service\AuditService',
            'ZF\Doctrine\Audit\Loader\AuditAutoloader' => 'ZF\Doctrine\Audit\Loader\AuditAutoloader',
        ),
        'initializers' => array(
            'ZF\Doctrine\Audit\Persistence\ObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditServiceInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditOptionsInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditEntitiesInitializer',
            'ZF\Doctrine\Audit\Persistence\AuthenticationServiceInitializer',
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'orm_zf_doctrine_audit' => array(
                'class' => 'Doctrine\\ORM\\Mapping\\Driver\\DriverChain',
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

        'connection' => array(
            'orm_zf_doctrine_audit' => array(
                'eventmanager' => 'orm_zf_doctrine_audit',
                'params' => array(
                    'charset' => 'utf8',
                ),
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

    'controllers' => array(
        'invokables' => array(
            'ZF\Doctrine\Audit\Controller\Index' => 'ZF\Doctrine\Audit\Controller\IndexController',
            'ZF\\Doctrine\\Audit\\Controller\\SchemaTool' => 'ZF\\Doctrine\\Audit\\Controller\\SchemaToolController',
        ),
        'initializers' => array(
            'ZF\Doctrine\Audit\Persistence\ObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditServiceInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditOptionsInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditEntitiesInitializer',
            'ZF\Doctrine\Audit\Persistence\AuthenticationServiceInitializer',
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'zf-doctrine-audit' => __DIR__ . '/../view',
        ),
    ),

    'view_helpers' => array(
        'invokables' => array(
            'auditCurrentRevisionEntity' => 'ZF\Doctrine\Audit\View\Helper\CurrentRevisionEntity',
            'auditEntityOptions' => 'ZF\Doctrine\Audit\View\Helper\EntityOptions',
            'auditRevisionEntityLink' => 'ZF\Doctrine\Audit\View\Helper\RevisionEntityLink',
            'auditRevisionPaginator' => 'ZF\Doctrine\Audit\View\Helper\RevisionPaginator',
            'auditRevisionEntityPaginator' => 'ZF\Doctrine\Audit\View\Helper\RevisionEntityPaginator',
            'auditAssociationSourcePaginator' => 'ZF\Doctrine\Audit\View\Helper\AssociationSourcePaginator',
            'auditAssociationTargetPaginator' => 'ZF\Doctrine\Audit\View\Helper\AssociationTargetPaginator',
            'auditOneToManyPaginator' => 'ZF\Doctrine\Audit\View\Helper\OneToManyPaginator',
        ),
        'initializers' => array(
            'ZF\Doctrine\Audit\Persistence\ObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditObjectManagerInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditServiceInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditOptionsInitializer',
            'ZF\Doctrine\Audit\Persistence\AuditEntitiesInitializer',
            'ZF\Doctrine\Audit\Persistence\AuthenticationServiceInitializer',
        ),
    ),

    'router' => array(
        'routes' => array(
            'audit' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/audit',
                    'defaults' => array(
                        'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'page' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:page]',
                            'constraints' => array(
                                'page' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'index',
                                'page' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                        ),
                    ),
                    'user' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/user[/:userId][/:page]',
                            'constraints' => array(
                                'userId' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'user',
                            ),
                        ),
                    ),

                    'revisions' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/revision[/:revisionId]',
                            'constraints' => array(
                                'revisionId' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'revision',
                                'revisionId' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                        ),
                    ),
                    'revision-entity' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/revision-entity[/:revisionEntityId][/:page]',
                            'constraints' => array(
                                'revisionEntityId' => '[0-9]*',
                                'page' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'revisionEntity',
                            ),
                        ),
                    ),
                    'one-to-many' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/one-to-many[/:revisionEntityId][/:joinTable][/:mappedBy][/:page]',
                            'constraints' => array(
                                'revisionEntityId' => '[0-9]*',
                                'page' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'one-to-many',
                            ),
                        ),
                    ),
                    'association-target' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/association-target[/:revisionEntityId][/:joinTable][/:page]',
                            'constraints' => array(
                                'revisionEntityId' => '[0-9]*',
                                'page' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'association-target',
                            ),
                        ),
                    ),
                    'association-source' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/association-source[/:revisionEntityId][/:joinTable][/:page]',
                            'constraints' => array(
                                'revisionEntityId' => '[0-9]*',
                                'page' => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'association-source',
                            ),
                        ),
                    ),
                    'entity' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/entity[/:entityClass][/:page]',
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action'     => 'entity',
                            ),
                        ),
                    ),
                    'compare' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/audit/compare',
                            'defaults' => array(
                                'controller' => 'ZF\Doctrine\Audit\Controller\Index',
                                'action' => 'compare',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'db-schema-tool-update' => array(
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
