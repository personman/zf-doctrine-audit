<?php

namespace ZF\Doctrine\Audit;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

class AuditOptionsFactory implements
    FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config')['zf-doctrine-audit'];

        $options = [
            'auditTableNamePrefix' => $config['audit_table_name_prefix'] ?? '',
            'auditTableNameSuffix' => $config['audit_table_name_suffix'] ?? '_audit',
            'epochImportLimit' => $config['epoch_import_limit'] ?? 200,
        ];

        $instance = new $requestedName($options);

        return $instance;
    }
}
