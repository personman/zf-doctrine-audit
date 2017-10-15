<?php

namespace ZF\Doctrine\Audit\EventListener;

use Doctrine\DBAL\Event\ConnectionEventArgs;

/**
 * Set the @zf_doctrine_audit_is_php variable.  When this is true
 * the postFlush event listener will close the audit revision.  When
 * false, such as from the command line, the audit revision will close
 * automatically for each insert, update, or delete
 */
final class PostConnect
{
    public function postConnect(ConnectionEventArgs $args)
    {
        $result = $args->getConnection()->query('SET @zf_doctrine_audit_is_php = TRUE;');
    }
}
