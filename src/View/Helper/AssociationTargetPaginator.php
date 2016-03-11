<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use ZF\Doctrine\Audit\Entity\AbstractAudit;

final class AssociationTargetPaginator extends AbstractHelper implements ServiceLocatorAwareInterface
{
    private $serviceLocator;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    public function __invoke($page, $revisionEntity, $joinTable)
    {
        $auditModuleOptions = $this->getServiceLocator()->getServiceLocator()->get('auditModuleOptions');
        $entityManager = $auditModuleOptions->getAuditObjectManager();
        $auditService = $this->getServiceLocator()->getServiceLocator()->get('ZF\Doctrine\Audit\Service\AuditService');

        foreach($auditService->getEntityAssociations($revisionEntity->getAuditEntity()) as $field => $value) {
            if (isset($value['joinTable']['name']) and $value['joinTable']['name'] == $joinTable) {
                $mapping = $value;
                break;
            }
        }

        $repository = $entityManager->getRepository('ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $joinTable));

        $qb = $repository->createQueryBuilder('association');
        $qb->andWhere('association.targetRevisionEntity = :var');
        $qb->setParameter('var', $revisionEntity);

        $adapter = new DoctrineAdapter(new ORMPaginator($qb));
        $paginator = new Paginator($adapter);
        $paginator->setDefaultItemCountPerPage($auditModuleOptions->getPaginatorLimit());

        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }
}