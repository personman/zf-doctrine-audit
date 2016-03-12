<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use ZF\Doctrine\Audit\Persistence;

class IndexController extends AbstractActionController implements
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface,
    Persistence\AuditEntitiesAwareInterface
{
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;
    use Persistence\AuditEntitiesAwareTrait;

    /**
     * Renders a paginated list of revisions.
     *
     * @param int $page
     */
    public function indexAction()
    {
        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');

        $viewModel = new ViewModel(
            [
            'page' => $page,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/index');

        return $viewModel;
    }

    /**
     * Renders a paginated list of revisions for the given user
     *
     * @param int $page
     */
    public function userAction()
    {
        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $userId = (int)$this->getEvent()->getRouteMatch()->getParam('userId');

        $user = $this->getObjectManager()
            ->getRepository($this->getAuditOptions()['user_entity_class_name'])
            ->find($userId);

        $viewModel = new ViewModel(
            [
            'page' => $page,
            'user' => $user,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/user');

        return $viewModel;
    }

    /**
     * Shows entities changed in the specified revision.
     *
     * @param integer $rev
     */
    public function revisionAction()
    {
        $revisionId = (int)$this->getEvent()->getRouteMatch()->getParam('revisionId');

        $revision = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\Revision')
            ->find($revisionId);

        if (!$revision) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $viewModel = new ViewModel(
            [
            'revision' => $revision,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/revision');

        return $viewModel;
    }

    /**
     * Show the detail for a specific revision entity
     */
    public function revisionEntityAction()
    {
        $this->mapAllAuditedClasses();

        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $revisionEntityId = (int) $this->getEvent()->getRouteMatch()->getParam('revisionEntityId');

        $revisionEntity = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId);

        if (!$revisionEntity) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $repository = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity');

        $viewModel = new ViewModel([
            'page' => $page,
            'revisionEntity' => $revisionEntity,
        ]);
        $viewModel->setTemplate('zf-doctrine-audit/index/revision-entity');

        return $viewModel;
    }

    /**
     * Lists revisions for the supplied entity.  Takes an audited entity class or audit class
     *
     * @param string $className
     * @param string $id
     */
    public function entityAction()
    {
        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $entityClass = $this->getEvent()->getRouteMatch()->getParam('entityClass');

        $viewModel = new ViewModel(
            [
            'entityClass' => $entityClass,
            'page' => $page,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/entity');

        return $viewModel;
    }

    /**
     * Compares an entity at 2 different revisions.
     *
     * @param  string   $className
     * @param  string   $id        Comma separated list of identifiers
     * @param  null|int $oldRev    if null, pulled from the posted data
     * @param  null|int $newRev    if null, pulled from the posted data
     * @return Response
     */
    public function compareAction()
    {
        $revisionEntityId_old = $this->getRequest()->getPost()->get('revisionEntityId_old');
        $revisionEntityId_new = $this->getRequest()->getPost()->get('revisionEntityId_new');

        $revisionEntity_old = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId_old);
        $revisionEntity_new = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId_new);

        if (!$revisionEntity_old and !$revisionEntity_new) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $viewModel = new ViewModel(
            [
            'revisionEntity_old' => $revisionEntity_old,
            'revisionEntity_new' => $revisionEntity_new,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/compare');

        return $viewModel;
    }

    public function oneToManyAction()
    {
        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $joinTable = $this->getEvent()->getRouteMatch()->getParam('joinTable');
        $revisionEntityId = $this->getEvent()->getRouteMatch()->getParam('revisionEntityId');
        $mappedBy = $this->getEvent()->getRouteMatch()->getParam('mappedBy');

        $revisionEntity = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId);

        if (!$revisionEntity) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $viewModel = new ViewModel(
            [
            'revisionEntity' => $revisionEntity,
            'page' => $page,
            'joinTable' => $joinTable,
            'mappedBy' => $mappedBy,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/one-to-many');

        return $viewModel;

    }

    public function associationSourceAction()
    {
        // When an association is requested all audit metadata must
        // be loaded in order to create the necessary join table
        // information
        $this->mapAllAuditedClasses();

#        $joinClasses = $moduleOptions->getJoinClasses();

        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $joinTable = $this->getEvent()->getRouteMatch()->getParam('joinTable');
        $revisionEntityId = $this->getEvent()->getRouteMatch()->getParam('revisionEntityId');

        $revisionEntity = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId);

        if (!$revisionEntity) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $viewModel = new ViewModel(
            [
            'revisionEntity' => $revisionEntity,
            'page' => $page,
            'joinTable' => $joinTable,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/association-source');

        return $viewModel;
    }

    public function associationTargetAction()
    {
        // When an association is requested all audit metadata must
        // be loaded in order to create the necessary join table
        // information
        $this->mapAllAuditedClasses();

        foreach ($this->getAuditEntities() as $className => $route) {
            $auditClassName = 'ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $className);
            $x = new $auditClassName;
        }
#        $joinClasses = $moduleOptions->getJoinClasses();

        $page = (int)$this->getEvent()->getRouteMatch()->getParam('page');
        $joinTable = $this->getEvent()->getRouteMatch()->getParam('joinTable');
        $revisionEntityId = $this->getEvent()->getRouteMatch()->getParam('revisionEntityId');

        $revisionEntity = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\\Entity\\RevisionEntity')->find($revisionEntityId);

        if (!$revisionEntity) {
            return $this->plugin('redirect')->toRoute('audit');
        }

        $viewModel = new ViewModel(
            [
            'revisionEntity' => $revisionEntity,
            'page' => $page,
            'joinTable' => $joinTable,
            ]
        );
        $viewModel->setTemplate('zf-doctrine-audit/index/association-target');

        return $viewModel;
    }

    private function mapAllAuditedClasses()
    {
        // When an association is requested all audit metadata must
        // be loaded in order to create the necessary join table
        // information

        foreach ($this->getAuditEntities() as $className => $route) {
            $auditClassName = 'ZF\Doctrine\Audit\\Entity\\' . str_replace('\\', '_', $className);
            $x = new $auditClassName;
        }
    }
}
