<?php

namespace ZF\Doctrine\Audit\Loader;

use Zend\Loader\StandardAutoloader;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;
use ZF\Doctrine\Audit\Persistence;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Collections\ArrayCollection;

class JoinTableAutoloader extends StandardAutoloader implements
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
        echo "Autoloading " . $auditClassName . "\n";

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
            return $this->loadJoinTableClass($auditClassName, $type);
        }

        // Get fields from target entity
        $metadataFactory = $this->getObjectManager()->getMetadataFactory();
        $auditedClassMetadata = $metadataFactory->getMetadataFor($className);
        $fields = $auditedClassMetadata->getFieldNames();
        $identifiers = $auditedClassMetadata->getFieldNames();

        $auditClass = new ClassGenerator();
        $auditClass->setNamespaceName("ZF\\Doctrine\\Audit\\RevisionEntity");
        $auditClass->setName(str_replace('\\', '_', $className));
        $auditClass->setExtendedClass('ZF\Doctrine\Audit\RevisionEntity\AbstractAudit');

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

    /**
     * An audit class takes the same namespace as the entity in which it is declared.
     * The table name for the Join Table is used as the class name.  This could cause
     * a problem if the join table name is the same as an entity name, but this is
     * very unlikely.
     */
    public function loadJoinTableClass($auditClassName, $type)
    {
        if (class_exists(str_replace('\\', '_', $auditClassName))) {
            die('duplicate class creation found!');
        }

        $namespaceParts = explode('\\', $auditClassName);
        $tableName = array_pop($namespaceParts);

        $foundJoinTable = false;
        foreach ($this->getAuditEntities() as $className => $classOptions) {
            // The same error will happen here for this invalid class name so catch and release
            try {
                $metadata = $this->getObjectManager()->getClassMetadata($className);
            } catch (MappingException $e) {
                continue;
            }

            foreach ($metadata->getAssociationMappings() as $mapping) {
                if (isset($mapping['joinTable'])) {
                    if ($mapping['joinTable']['name'] == $tableName) {
                        $foundJoinTable = true;
                        break;
                    }
                }
            }

            if ($foundJoinTable) {
                break;
            }
        }

        if (! $foundJoinTable) {
            return false;
        }

        $auditClass = new ClassGenerator();
        $auditClass->setNamespaceName("ZF\\Doctrine\\Audit\\RevisionEntity");
        $auditClass->setName(str_replace('\\', '_', $auditClassName));
        $auditClass->setExtendedClass('ZF\Doctrine\Audit\RevisionEntity\AbstractAudit');

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

        $fields = [];
        foreach ($mapping['joinTable']['joinColumns'] as $column) {
            $column['dataType'] = $this->getObjectManager()
                ->getClassMetadata($mapping['sourceEntity'])
                ->getTypeOfField($column['referencedColumnName'])
                ;
            $fields[] = $column;
        }
        foreach ($mapping['joinTable']['inverseJoinColumns'] as $column) {
            $column['dataType'] = $this->getObjectManager()
                ->getClassMetadata($mapping['targetEntity'])
                ->getTypeOfField($column['referencedColumnName'])
                ;
            $fields[] = $column;
        }

        // Generate audit entity
        foreach ($fields as $field) {
            $auditClass->addProperty($field['name'], null, PropertyGenerator::FLAG_PROTECTED);
        }

        $auditClass->addMethod(
            'getAssociationMappings',
            array(),
            MethodGenerator::FLAG_PUBLIC,
            "return unserialize('" . serialize($mapping) . "');"
        );

        // Add exchange array method
        $setters = array();
        foreach ($fields as $field) {
            $setters[] = '$this->' . $field['name']
                . ' = (isset($data["' . $field['name'] . '"])) ? $data["' . $field['name'] . '"]: null;';
            $arrayCopy[] = "    \"$fieldName\"" . ' => $this->' . $field['name'];
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
            " return '" .  addslashes($auditClassName) . "';"
        );

        eval($auditClass->generate());

        return true;
    }
}
