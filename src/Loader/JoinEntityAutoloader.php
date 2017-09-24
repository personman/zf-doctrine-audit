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

final class JoinEntityAutoloader extends StandardAutoloader implements
    Persistence\JoinEntityConfigCollectionAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\JoinEntityConfigCollectionAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;

    /**
     * Dynamically scope an audit class
     */
    public function loadClass($auditClassName, $type)
    {
        $foundClassName = false;
        foreach ($this->getJoinEntityConfigCollection() as $className => $config) {
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

        $metadata = $this->getObjectManager()->getClassMetadata($config['ownerEntity']);

        foreach ($metadata->getAssociationMappings() as $mapping) {
            if (isset($mapping['joinTable'])) {
                if ($mapping['joinTable']['name'] == $config['tableName']) {
                    $foundJoinEntity = true;
                    break;
                }
            }
        }

        if (! $foundJoinEntity) {
            return false;
        }

        $auditClass = new ClassGenerator();
        $auditClass->setNamespaceName("ZF\\Doctrine\\Audit\\RevisionEntity");
        $auditClass->setName($auditClassName);
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
                ->getTypeOfField($column['referencedColumnName']);
            $fields[] = $column;
        }
        foreach ($mapping['joinTable']['inverseJoinColumns'] as $column) {
            $column['dataType'] = $this->getObjectManager()
                ->getClassMetadata($mapping['targetEntity'])
                ->getTypeOfField($column['referencedColumnName']);
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

        // echo "Created " . $auditClass->getName() . "\n" . $auditClass->getNamespaceName() . "\n";

        eval($auditClass->generate());

        return true;
    }
}
