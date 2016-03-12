<?php

namespace ZF\Doctrine\Audit\Persistence;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuditOptionsInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof AuditOptionsAwareInterface) {
            if (method_exists($serviceLocator, 'getServiceLocator')) {
                $serviceLocator = $serviceLocator->getServiceLocator();
            }

            $config = $serviceLocator->get('Config')['zf-doctrine-audit'];

            $options = [
                'datetime_format' => $config['datetime_format'] ?? 'r',
                'paginator_limit' => $config['paginator_limit'] ?? 20,
                'audit_table_name_prefix' => $config['audit_table_name_prefix'] ?? '',
                'audit_table_name_suffix' => $config['audit_table_name_suffix'] ?? '_audit',
                'revision_table_name' => $config['revision_table_name'] ?? 'Revision',
                'revision_entity_table_name' => $config['revision_entity_table_name'] ?? 'RevisionEntity',
                'user_entity_class_name' => $config['user_entity_class_name'] ?? '',
                'epoch_import_limit' => $config['epoch_import_limit'] ?? 200,
            ];

            $instance->setAuditOptions($options);
        }
    }
}
