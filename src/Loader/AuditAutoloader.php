<?php

namespace ZF\Doctrine\Audit\Loader;

use Zend\Loader\StandardAutoloader;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use ZF\Doctrine\Audit\Persistence;

class AuditAutoloader extends StandardAutoloader implements
    Persistence\AuditEntitiesAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;

    /**
     * Dynamically scope an audit class
     */
    public function loadClass($auditClassName, $type)
    {
        $foundClassName = false;
        foreach ($this->getAuditEntities() as $className => $classOptions) {
            if ($this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                ->generateClassName($className) == $auditClassName) {

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
        $auditClass->setNamespaceName("ZF\\Doctrine\\Audit\\RevisionEntity");
        $auditClass->setName(str_replace('\\', '_', $className));
        $auditClass->setExtendedClass('AbstractAudit');

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
            " return '" .  addslashes($className) . "';"
        );

        eval($auditClass->generate());

        return true;
    }
}
