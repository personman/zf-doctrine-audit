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

                $this->getAuditObjectManager()->persist($auditEntity);
                $this->getAuditObjectManager()->persist($targetEntity);
            }
        }

        // Add revision types
        foreach (array('insert', 'update', 'delete', 'epoch') as $type) {
            $revisionType = $this->getAuditObjectManager()
                ->getRepository('ZF\Doctrine\Audit\Entity\RevisionType')
                ->findOneBy([
                    'revisionType' => $type,
                ]);

            if (! $revisionType) {
                $revisionType = new Entity\RevisionType();
                $revisionType->setRevisionType($type);

                $this->getAuditObjectManager()->persist($revisionType);
            }
        }

        $this->getAuditObjectManager()->flush();

        $console->write("Audit data fixture import complete");
    }
}
