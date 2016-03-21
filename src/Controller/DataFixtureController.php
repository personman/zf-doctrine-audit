<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use DoctrineDataFixtureModule\Loader\ServiceLocatorAwareLoader;
use RuntimeException;
use Doctrine\ORM\Tools\SchemaTool;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\Entity;
use DateTime;
use Exception;

class DataFixtureController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;

    public function importAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $config = $this->getServiceLocator()->get('Config')['zf-doctrine-audit'];

        // Create a revision to associate with field revision
        $revision = new Entity\Revision();
        $revision->setCreatedAt(new DateTime());
        $revision->setComment('Data Fixture Import');

        $this->getAuditObjectManager()->persist($revision);

        // Add FieldStatus
        foreach (array('active', 'inactive') as $name) {
            $fieldStatus = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\FieldStatus')
                ->findOneBy([
                    'name' => $name,
                ]);

            if (! $fieldStatus) {
                $fieldStatus = new Entity\FieldStatus();
                $fieldStatus->setName($name);

                // Save active for column and association mappings
                if ($name == 'active') {
                    $fieldStatusActive = $fieldStatus;
                }

                $this->getAuditObjectManager()->persist($fieldStatus);
            }
        }

        foreach ($config['entities'] as $className => $route) {
            $targetEntity = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
                ->findOneBy(['name' => $className]);

            if (! $targetEntity) {
                $auditEntityClassName = $this->getAuditObjectManager()
                    ->getRepository('ZF\Doctrine\Audit\Entity\AuditEntity')
                    ->generateClassName($className)
                    ;

                $auditEntity = new Entity\AuditEntity();
                $auditEntity->setName($auditEntityClassName);
                $auditEntity->setTableName(
                    $this->getAuditObjectManager()
                        ->getClassMetadata($auditEntityClassName)
                        ->getTableName()
                );

                $targetEntity = new Entity\TargetEntity();
                $targetEntity->setAuditEntity($auditEntity);
                $targetEntity->setName($className);
                $targetEntity->setTableName(
                    $this->getObjectManager()
                        ->getClassMetadata($className)
                        ->getTableName()
                );

                $identifiers = $this->getObjectManager()
                    ->getClassMetadata($className)
                    ->getIdentifierFieldNames()
                    ;

                foreach ($identifiers as $fieldName) {
                    $identifier = new Entity\Identifier();
                    $identifier->setTargetEntity($targetEntity);
                    $identifier->setFieldName($fieldName);
                    $identifier->setColumnName(
                        $this->getObjectManager()
                            ->getClassMetadata($className)
                            ->getColumnName($fieldName)
                    );

                    $this->getAuditObjectManager()->persist($identifier);
                }

                // Add Fields
                $fields = $this->getObjectManager()
                    ->getClassMetadata($className)
                    ->getFieldNames()
                    ;

                foreach ($fields as $fieldName) {
                    $field = new Entity\Field();
                    $field->setTargetEntity($targetEntity);
                    $field->setName($fieldName);
                    $field->setColumnName(
                        $this->getObjectManager()
                            ->getClassMetadata($className)
                            ->getColumnName($fieldName)
                    );

                    $fieldRevision = new Entity\FieldRevision();
                    $fieldRevision->setFieldStatus($fieldStatusActive);
                    $fieldRevision->setField($field);
                    $fieldRevision->setRevision($revision);

                    $this->getAuditObjectManager()->persist($field);
                    $this->getAuditObjectManager()->persist($fieldRevision);
                }

                // Add Associations to Fields
                $associations = $this->getObjectManager()
                    ->getClassMetadata($className)
                    ->getAssociationNames()
                    ;

                foreach ($associations as $fieldName) {
                    $associationMapping = $this->getObjectManager()
                        ->getClassMetadata($className)
                        ->getAssociationMapping($fieldName);

                    if (! isset($associationMapping['joinColumns'])) {
                        continue;
                    }

                    if (sizeof($associationMapping['joinColumns']) != 1) {
                        throw new Exception('Unable to handle > 1 join column per association');
                    }

                    $field = new Entity\Field();
                    $field->setTargetEntity($targetEntity);
                    $field->setName($fieldName);
                    $field->setColumnName(array_shift($associationMapping['joinColumns'])['name']);

                    $fieldRevision = new Entity\FieldRevision();
                    $fieldRevision->setFieldStatus($fieldStatusActive);
                    $fieldRevision->setField($field);
                    $fieldRevision->setRevision($revision);

                    $this->getAuditObjectManager()->persist($field);
                    $this->getAuditObjectManager()->persist($fieldRevision);
                }

                $this->getAuditObjectManager()->persist($auditEntity);
                $this->getAuditObjectManager()->persist($targetEntity);
            }
        }

        // Add RevisionType
        foreach (array('insert', 'update', 'delete', 'epoch') as $name) {
            $revisionType = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy([
                    'name' => $name,
                ]);

            if (! $revisionType) {
                $revisionType = new Entity\RevisionType();
                $revisionType->setName($name);

                $this->getAuditObjectManager()->persist($revisionType);
            }
        }

        $this->getAuditObjectManager()->flush();

        $console->write("Audit data fixture import complete", Color::GREEN);
    }
}
