<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\Entity;
use DateTime;

class FieldController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;

    public function deactivateAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $fieldName = $this->params()->fromRoute('field');
        $entityName = $this->params()->fromRoute('entity');
        $comment = $this->params()->fromRoute('comment');

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder->select('field')
            ->from('ZF\Doctrine\Audit\Entity\Field', 'field')
            ->innerJoin('field.targetEntity', 'targetEntity')
            ->andWhere('field.name = :fieldName')
            ->andWhere('targetEntity.name = :entityName')
            ->setParameter('entityName', $entityName)
            ->setParameter('fieldName', $fieldName)
            ;

        $field = $queryBuilder->getQuery()->getOneOrNullResult();

        if (! $field) {
            $console->write("Field was not found\n", Color::RED);

            return;
        }

        $fieldStatus = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\FieldStatus')
            ->findOneBy([
                'name' => 'inactive',
            ]);

        $revision = new Entity\Revision();
        $revision->setCreatedAt(new DateTime());
        $revision->setComment($comment ?? 'Deactivated field from console command');
        $this->getAuditObjectManager()->persist($revision);

        $fieldRevision = new Entity\FieldRevision();
        $fieldRevision->setFieldStatus($fieldStatus);
        $fieldRevision->setField($field);
        $fieldRevision->setRevision($revision);
        $this->getAuditObjectManager()->persist($fieldRevision);

        $this->getAuditObjectManager()->flush();

        $console->write("Field has been deactivated\n", Color::GREEN);
    }

    public function activateAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $fieldName = $this->params()->fromRoute('field');
        $entityName = $this->params()->fromRoute('entity');
        $comment = $this->params()->fromRoute('comment');

        $queryBuilder = $this->getAuditObjectManager()->createQueryBuilder();
        $queryBuilder->select('field')
            ->from('ZF\Doctrine\Audit\Entity\Field', 'field')
            ->innerJoin('field.targetEntity', 'targetEntity')
            ->andWhere('field.name = :fieldName')
            ->andWhere('targetEntity.name = :entityName')
            ->setParameter('entityName', $entityName)
            ->setParameter('fieldName', $fieldName)
            ;

        $field = $queryBuilder->getQuery()->getOneOrNullResult();

        if (! $field) {
            $console->write("Field was not found\n", Color::RED);

            return;
        }

        $fieldStatus = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\FieldStatus')
            ->findOneBy([
                'name' => 'active',
            ]);

        $revision = new Entity\Revision();
        $revision->setCreatedAt(new DateTime());
        $revision->setComment($comment ?? 'Activated field from console command');
        $this->getAuditObjectManager()->persist($revision);

        $fieldRevision = new Entity\FieldRevision();
        $fieldRevision->setFieldStatus($fieldStatus);
        $fieldRevision->setField($field);
        $fieldRevision->setRevision($revision);
        $this->getAuditObjectManager()->persist($fieldRevision);

        $this->getAuditObjectManager()->flush();

        $console->write("Field has been activated\n", Color::GREEN);
    }
}
