<?php

namespace ZF\Doctrine\Audit\Loader;

use Zend\Loader\StandardAutoloader;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use ZF\Doctrine\Audit\Persistence;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Collections\ArrayCollection;
use ZF\Doctrine\Audit\Entity;

class EntityAutoloader extends StandardAutoloader implements
    Persistence\EntityConfigCollectionAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\EntityConfigCollectionAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;

    /**
     * Dynamically scope an audit class
     */
    public function loadClass($auditClassName, $type)
    {
        $foundClassName = false;
        foreach ($this->getEntityConfigCollection() as $className => $classOptions) {
            if ($this->getAuditObjectManager()
                ->getRepository(Entity\AuditEntity::class)
                ->generateClassName($className) == $auditClassName
            ) {
                $foundClassName = true;
                break;
            }
        }

        if (! $foundClassName) {
            return false;
        }

        // Get fields from target entity
        $metadataFactory = $this->getObjectManager()->getMetadataFactory();
        $auditedClassMetadata = $metadataFactory->getMetadataFor($className);
        $fields = $auditedClassMetadata->getFieldNames();
        $identifiers = $auditedClassMetadata->getFieldNames();

        $auditClass = new ClassGenerator();
        $auditClass->setNamespaceName("ZF\\Doctrine\\Audit\\AuditEntity");
        $auditClass->setName(str_replace('\\', '_', $className));
        $auditClass->setExtendedClass("ZF\\Doctrine\\Audit\\AuditEntity\\AbstractAudit");

        // Add revision reference getter and setter
        $auditClass->addProperty('revisionEntity', null, PropertyGenerator::FLAG_PROTECTED);
        $auditClass->addMethod(
            'getRevisionEntity',
            [],
            MethodGenerator::FLAG_PUBLIC,
            " return \$this->revisionEntity;"
        );

        $auditClass->addMethod(
            'setRevisionEntity',
            ['value'],
            MethodGenerator::FLAG_PUBLIC,
            " \$this->revisionEntity = \$value;\n\nreturn \$this;
            "
        );

        // Generate audit entity
        foreach ($fields as $field) {
            $auditClass->addProperty($field, null, PropertyGenerator::FLAG_PROTECTED);
        }

        foreach ($auditedClassMetadata->getAssociationNames() as $associationName) {
            $auditClass->addProperty($associationName, null, PropertyGenerator::FLAG_PROTECTED);
            $fields[] = $associationName;
        }

        $auditClass->addMethod(
            'getAssociationMappings',
            [],
            MethodGenerator::FLAG_PUBLIC,
            "return unserialize('" . serialize($auditedClassMetadata->getAssociationMappings()) . "');"
        );

        // Add exchange array method
        $setters = [];
        foreach ($fields as $fieldName) {
            $setters[] = '$this->' . $fieldName
                . ' = (isset($data["' . $fieldName . '"])) ? $data["' . $fieldName . '"]: null;';
            $arrayCopy[] = "    \"$fieldName\"" . ' => $this->' . $fieldName;
        }

        $auditClass->addMethod(
            'getArrayCopy',
            [],
            MethodGenerator::FLAG_PUBLIC,
            "return array(\n" . implode(",\n", $arrayCopy) . "\n);"
        );

        $auditClass->addMethod(
            'exchangeArray',
            ['data'],
            MethodGenerator::FLAG_PUBLIC,
            implode("\n", $setters)
        );

        // Add function to return the entity class this entity audits
        $auditClass->addMethod(
            'getAuditedEntityClass',
            [],
            MethodGenerator::FLAG_PUBLIC,
            " return '" . addslashes($className) . "';"
        );

        eval($auditClass->generate());

        return true;
    }
}
