<?php

namespace ZF\Doctrine\Audit\Loader;

use Zend\Loader\StandardAutoloader;
use Zend\ServiceManager\ServiceManager;
use Zend\Code\Reflection\ClassReflection;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use ZF\Doctrine\Audit\Persistence;

class AuditAutoloader extends StandardAutoloader implements
    Persistence\AuditEntitiesAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface,
    Persistence\AuditServiceAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;
    use Persistence\AuditServiceAwareTrait;

    /**
     * Dynamically scope an audit class
     *
     * @param  string $className
     * @return false|string
     */
    public function loadClass($className, $type)
    {
        $auditClass = new ClassGenerator();

        // Add revision reference getter and setter
        $auditClass->addProperty('revisionEntity', null, PropertyGenerator::FLAG_PROTECTED);
        $auditClass->addMethod(
            'getRevisionEntity',
            array(),
            MethodGenerator::FLAG_PUBLIC,
            " return \$this->revisionEntity;"
        );

        $auditClass->addMethod(
            'setRevisionEntity',
            array('value'),
            MethodGenerator::FLAG_PUBLIC,
            " \$this->revisionEntity = \$value;\nreturn \$this;
            "
        );

        // Verify this autoloader is used for target class
        foreach ($this->getAuditEntities() as $targetClass => $targetClassOptions) {
             $auditClassName = 'ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $targetClass);

            if ($auditClassName == $className) {
                $currentClass = $targetClass;
            }
             $autoloadClasses[] = $auditClassName;
        }
        if (!in_array($className, $autoloadClasses)) {
            return;
        }

        // Get fields from target entity
        $metadataFactory = $this->getObjectManager()->getMetadataFactory();
        $auditedClassMetadata = $metadataFactory->getMetadataFor($currentClass);
        $fields = $auditedClassMetadata->getFieldNames();
        $identifiers = $auditedClassMetadata->getFieldNames();

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
            array(),
            MethodGenerator::FLAG_PUBLIC,
            "return unserialize('" . serialize($auditedClassMetadata->getAssociationMappings()) . "');"
        );

        // Add exchange array method
        $setters = array();
        foreach ($fields as $fieldName) {
            $setters[] = '$this->' . $fieldName
                . ' = (isset($data["' . $fieldName . '"])) ? $data["' . $fieldName . '"]: null;';
            $arrayCopy[] = "    \"$fieldName\"" . ' => $this->' . $fieldName;
        }

        $auditClass->addMethod(
            'getArrayCopy',
            array(),
            MethodGenerator::FLAG_PUBLIC,
            "return array(\n" . implode(",\n", $arrayCopy) . "\n);"
        );

        $auditClass->addMethod(
            'exchangeArray',
            array('data'),
            MethodGenerator::FLAG_PUBLIC,
            implode("\n", $setters)
        );

        // Add function to return the entity class this entity audits
        $auditClass->addMethod(
            'getAuditedEntityClass',
            array(),
            MethodGenerator::FLAG_PUBLIC,
            " return '" .  addslashes($currentClass) . "';"
        );

        $auditClass->setNamespaceName("ZF\Doctrine\Audit\\Entity");
        $auditClass->setName(str_replace('\\', '_', $currentClass));
        $auditClass->setExtendedClass('AbstractAudit');

        $auditedClassMetadata = $metadataFactory->getMetadataFor($currentClass);

        foreach ($auditedClassMetadata->getAssociationMappings() as $mapping) {
            if (isset($mapping['joinTable']['name'])) {
                $auditJoinTableClassName = "ZF\Doctrine\Audit\\Entity\\"
                    . str_replace('\\', '_', $mapping['joinTable']['name']);
                $auditEntities[] = $auditJoinTableClassName;
            }
        }

        eval($auditClass->generate());

        return true;
    }
}
