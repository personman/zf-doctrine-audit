<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\Tools\RevisionAuditTool;

interface RevisionAuditToolAwareInterface
{
    public function setRevisionAuditTool(RevisionAuditTool $revisionAuditTool);
    public function getRevisionAuditTool(): RevisionAuditTool;
}
