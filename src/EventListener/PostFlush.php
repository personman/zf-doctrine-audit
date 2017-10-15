<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;
use ZF\Doctrine\Audit\Tools\RevisionAuditTool;
use ZF\Doctrine\Audit\Persistence\RevisionAuditToolAwareInterface;
use ZF\Doctrine\Audit\Persistence\RevisionAuditToolAwareTrait;

/**
 * After each change to the database the revision entity stays
 * "open" and mapped to the mysql connection.  This listener
 * must run after every flush() event to close the revision on
 * the current connection thereby completeing the auditing transaction.
 *
 * This class may be overridden via the service_manager
 * in order to implement your custom identity for revision
 * auditing.  You will still need to use native query
 * because doctrine createQuery expects a FROM clause.
 */
final class PostFlush implements RevisionAuditToolAwareInterface
{
    use RevisionAuditToolAwareTrait;

    private $enable = true;

    public function __construct(RevisionAuditTool $revisionAuditTool)
    {
        $this->setRevisionAuditTool($revisionAuditTool);
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if ($this->enable) {
            $this->getRevisionAuditTool()->close();
        }
    }

    public function enable()
    {
        $this->enable = true;
    }

    public function disable()
    {
        $this->enable = false;
    }
}
