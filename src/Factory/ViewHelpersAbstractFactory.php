<?php

namespace ZF\Doctrine\Audit\Factory;

use Zend\ServiceManager\ServiceLocatorInterface;

class ViewHelpersAbstractFactory extends AbstractAbstractFactory
{
    protected $factoryClasses = [
        'auditCurrentRevisionEntity' =>
            'ZF\Doctrine\Audit\View\Helper\CurrentRevisionEntity',
        'auditEntityOptions' => 'ZF\Doctrine\Audit\View\Helper\EntityOptions',
        'auditRevisionEntityLink' => 'ZF\Doctrine\Audit\View\Helper\RevisionEntityLink',
        'auditRevisionPaginator' => 'ZF\Doctrine\Audit\View\Helper\RevisionPaginator',
        'auditRevisionEntityPaginator' =>
            'ZF\Doctrine\Audit\View\Helper\RevisionEntityPaginator',
        'auditAssociationSourcePaginator' =>
            'ZF\Doctrine\Audit\View\Helper\AssociationSourcePaginator',
        'auditAssociationTargetPaginator' =>
            'ZF\Doctrine\Audit\View\Helper\AssociationTargetPaginator',
        'auditOneToManyPaginator' => 'ZF\Doctrine\Audit\View\Helper\OneToManyPaginator',
        'auditDateTimeFormatter' => 'ZF\Doctrine\Audit\View\Helper\DateTimeFormatter',
        'auditService' => 'ZF\Doctrine\Audit\Service\AuditService',
    ];
}
