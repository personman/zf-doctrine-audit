<?php

namespace ZF\Doctrine\Audit\View\Helper;

use Zend\View\Helper\AbstractHelper;
use ZF\Doctrine\Audit\Persistence;

// Return the latest revision entity for the given entity
final class CurrentRevisionEntity extends AbstractHelper implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditServiceAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\AuditServiceAwareTrait;

    public function __invoke($entity)
    {
        $revisionEntities = $this->getAuditObjectManager()
            ->getRepository('ZF\\Doctrine\\Audit\\Entity\\RevisionEntity')
            ->findBy(
                array(
                'targetEntityClass' => get_class($entity),
                'entityKeys' => json_encode($this->getAuditService()->getEntityIdentifierValues($entity), JSON_NUMERIC_CHECK),
                ),
                array('id' => 'DESC'),
                1
            );

        if (sizeof($revisionEntities)) {
            return $revisionEntities[0];
        }
    }
}
