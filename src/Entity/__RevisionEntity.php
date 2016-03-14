<?php

namespace ZF\Doctrine\Audit\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Zend\Code\Reflection\ClassReflection;
use Exception;

class RevisionEntity
{
    protected $id;

    // Foreign key to the revision
    protected $revision;

    // An array of primary keys
    protected $entityKeys;

    // The name of the audit entity
    protected $auditEntityClass;

    // The name of the entity which is audited
    protected $targetEntityClass;

    // The type of action, INS, UPD, DEL
    protected $revisionType;

    // Fetched from entity::getAuditTitle() if exists
    protected $title;

    public function getId()
    {
        return $this->id;
    }

    public function setRevision(Revision $revision)
    {
        $this->revision = $revision;

        return $this;
    }

    public function getAuditEntityClass()
    {
        return $this->auditEntityClass;
    }

    public function setAuditEntityClass(string $value)
    {
        $this->auditEntityClass = $value;

        return $this;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setTargetEntityClass(string $value)
    {
        $this->targetEntityClass = $value;

        return $this;
    }

    public function getTargetEntityClass()
    {
        return $this->targetEntityClass;
    }

    public function getEntityKeys()
    {
        return json_decode($this->entityKeys, true);
    }

    public function setEntityKeys(array $value)
    {
        unset($value['revisionEntity']);

        foreach ($value as $key => $val) {
            $value[$key] = $val;
        }

        $this->entityKeys = json_encode($value, JSON_NUMERIC_CHECK);

        return $this;
    }

    public function getRevisionType()
    {
        return $this->revisionType;
    }

    public function setRevisionType($value)
    {
        $this->revisionType = $value;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $value)
    {
        $this->title = substr($value, 0, 255);

        return $this;
    }
}
