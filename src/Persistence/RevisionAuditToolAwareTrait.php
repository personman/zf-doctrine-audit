<?php

namespace ZF\Doctrine\Audit\Persistence;

use ZF\Doctrine\Audit\Tools\RevisionAuditTool;

trait RevisionAuditToolAwareTrait
{
    protected $revisionAuditTool;

    public function setRevisionAuditTool(RevisionAuditTool $revisionAuditTool)
    {
        $this->revisionAuditTool = $revisionAuditTool;

        return $this;
    }

    public function getRevisionAuditTool(): RevisionAuditTool
    {
        return $this->revisionAuditTool;
    }
}
