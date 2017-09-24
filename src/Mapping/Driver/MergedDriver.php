<?php

/**
 * Because we want to divide the work of Entity and JoinEntity
 * driving this class allows both drivers to work in tandem.
 */

namespace ZF\Doctrine\Audit\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use ZF\Doctrine\Audit\Persistence;
use Exception;

class MergedDriver implements
    MappingDriver,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;

    private $drivers = [];

    public function addDriver(MappingDriver $driver): self
    {
        $this->drivers[] = $driver;

        return $this;
    }

    public function register(): self
    {
        // Driver for audited entities
        $this->getAuditObjectManager()
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->addDriver($this, 'ZF\Doctrine\Audit\RevisionEntity');

        return $this;
    }

    /**
     * Load the metadata for the specified class into the provided container.
     *
     * @param string        $className
     * @param ClassMetadata $metadata
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        foreach ($this->drivers as $driver) {
            if (in_array($className, $driver->getAllClassNames())) {
                return $driver->loadMetadataForClass($className, $metadata);
            }
        }
    }

    /**
     * Gets the names of all mapped classes known to this driver.
     *
     * @return array The names of all mapped classes known to this driver.
     */
    public function getAllClassNames(): array
    {
        $classNames = [];
        foreach ($this->drivers as $driver) {
            $classNames = array_merge($classNames, $driver->getAllClassNames());
        }

        return $classNames;
    }

    /**
     * Whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped as an Entity or a
     * MappedSuperclass.
     *
     * @param  string $className
     * @return boolean
     */
    public function isTransient($className): bool
    {
        return true;
    }
}
