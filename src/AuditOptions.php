<?php

namespace ZF\Doctrine\Audit;

use Zend\Stdlib\AbstractOptions;

class AuditOptions extends AbstractOptions
{
    protected $auditTableNamePrefix;

    protected $auditTableNameSuffix;

    protected $revisionTableName;

    protected $revisionEntityTableName;

    protected $epochImportLimit;

    public function getAuditTableNamePrefix()
    {
        return $this->auditTableNamePrefix;
    }

    protected function setAuditTableNamePrefix($auditTableNamePrefix)
    {
        $this->auditTableNamePrefix = $auditTableNamePrefix;

        return $this;
    }

    public function getAuditTableNameSuffix()
    {
        return $this->auditTableNameSuffix;
    }

    protected function setAuditTableNameSuffix($auditTableNameSuffix)
    {
        $this->auditTableNameSuffix = $auditTableNameSuffix;

        return $this;
    }

    public function getRevisionTableName()
    {
        return $this->revisionTableName;
    }

    protected function setRevisionTableName($revisionTableName)
    {
        $this->revisionTableName = $revisionTableName;

        return $this;
    }

    public function getRevisionEntityTableName()
    {
        return $this->revisionEntityTableName;
    }

    protected function setRevisionEntityTableName($revisionEntityTableName)
    {
        $this->revisionEntityTableName = $revisionEntityTableName;

        return $this;
    }

    public function getEpochImportLimit()
    {
        return $this->epochImportLimit;
    }

    protected function setEpochImportLimit($epochImportLimit)
    {
        $this->epochImportLimit = $epochImportLimit;

        return $this;
    }
}