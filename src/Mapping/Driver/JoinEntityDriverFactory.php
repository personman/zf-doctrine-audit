<?php

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

class JoinEntityDriverFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $auditOptions = [
            'audit_table_name_prefix' => $config['audit_table_name_prefix'] ?? '',
            'audit_table_name_suffix' => $config['audit_table_name_suffix'] ?? '_audit',
            'revision_table_name' => $config['revision_table_name'] ?? 'Revision',
            'revision_entity_table_name' => $config['revision_entity_table_name'] ?? 'RevisionEntity',
            'user_entity_class_name' => $config['user_entity_class_name'] ?? '',
            'epoch_import_limit' => $config['epoch_import_limit'] ?? 200,
        ];

        $instance = new $requestedName();
        $instance->setJoinEntityConfigCollection(new ArrayCollection($config['joinEntities']));
        $instance->setObjectManager($container->get($config['target_object_manager']));
        $instance->setAuditObjectManager($container->get($config['audit_object_manager']));
        $instance->setAuditOptions($auditOptions);

        return $instance;
    }
}