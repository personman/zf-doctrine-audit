<?php

namespace ZF\Doctrine\Audit\Repository;

use Doctrine\ORM\EntityRepository;
use ZF\Doctrine\Audit\Entity;

class RevisionEntityRepository extends EntityRepository
{
    public function getRevisionEntityIdentifierValue(Entity\RevisionEntity $revisionEntity)
    {
        $keys = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\RevisionEntityIdentifierValue')
            ->findBy([
                'revisionEntity' => $revisionEntity
            ]);
    }
}
