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
use Zend\Code\Reflection\ClassReflection;
use DateTime;
use ZF\Doctrine\Audit\Entity;

class EpochController extends AbstractActionController implements
    Persistence\AuditEntitiesAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface,
    Persistence\AuditServiceAwareInterface
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;
    use Persistence\AuditServiceAwareTrait;

    public function indexAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $console->write("********************************************************************************\n", Color::GREEN);
        $console->write("* This epoch tool will populate the zf-apigility-audit auditing tables with a  *\n", Color::GREEN);
        $console->write("* snapshot of the database as it exists at this point in time.  The audit      *\n", Color::GREEN);
        $console->write("* tables must be empty when this tool is ran.  In order to retire a set of     *\n", Color::GREEN);
        $console->write("* audit tables you may continue the audit trail by setting the auto_increment  *\n", Color::GREEN);
        $console->write("* values of the Revision and RevisionEntity tables to the next id in series    *\n", Color::GREEN);
        $console->write("********************************************************************************\n", Color::GREEN);

        // Get connection from target object manager
        // for each entity in target object manager

        $revisionNumber = 0;
        foreach ($this->getAuditEntities() as $className => $classInfo) {

            $auditEntityClass = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', $className);

            $revisionNumber ++;
            $revision = new Entity\Revision();
            $revision->setTimestamp(new \DateTime());
            $revision->setComment('Epoch');
            $this->getAuditObjectManager()->persist($revision);

            foreach ($this->getObjectManager()->getRepository($className)->findAll() as $entity) {

                $revisionEntity = new Entity\RevisionEntity();
                $revisionEntity->setRevision($revision);

                $revisionEntity->setRevisionType('EPO');
                $revisionEntity->setAuditEntityClass($auditEntityClass);
                $revisionEntity->setTargetEntityClass($className);
                $revisionEntity->setEntityKeys($this->getAuditService()->getEntityIdentifierValues($entity));

                if (method_exists($entity, '__toString')) {
                    $revisionEntity->setTitle((string) $entity);
                }
                echo $revisionEntity->getTitle();

                $auditEntity = new $auditEntityClass();
                $auditEntity->exchangeArray($this->getClassProperties($entity));
                $auditEntity->setRevisionEntity($revisionEntity);

                $this->getAuditObjectManager()->persist($revisionEntity);
                $this->getAuditObjectManager()->persist($auditEntity);

            }
            // when to flush?

            $this->getAuditObjectManager()->flush();
            break;
        }
    }

    // Reflect audited entity properties
    private function getClassProperties($entity)
    {
        $properties = array();

        $reflectedAuditedEntity = new ClassReflection($entity);

        // Get mapping from metadata

        foreach ($reflectedAuditedEntity->getProperties() as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($entity);

            // If a property is an object we probably are not mapping that to
            // a field.  Do no special handing...
            if ($value instanceof PersistentCollection) {
            }

            // Set values to getId for classes
            if (gettype($value) == 'object' and method_exists($value, 'getId')) {
                $value = $value->getId();
            }

            $properties[$property->getName()] = $value;
        }

        return $properties;
    }
}
