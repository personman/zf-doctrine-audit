<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;

class PostFlush
{
    public function postFlush(PostFlushEventArgs $args)
    {
        $resultSetMapping = new ResultSetMapping();
        $query = $args->getEntityManager()
            ->createNativeQuery("SELECT close_revision_audit(1, 'test', 'test@test', 'comment')", $resultSetMapping);

       $query->getResult();
    }
}