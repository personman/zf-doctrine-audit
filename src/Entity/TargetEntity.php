<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * TargetEntity
 */
class TargetEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\Doctrine\Audit\Entity\AuditEntity
     */
    private $auditEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revisionEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $identifier;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->revisionEntity = new \Doctrine\Common\Collections\ArrayCollection();
        $this->identifier = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return TargetEntity
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set tableName
     *
     * @param string $tableName
     *
     * @return TargetEntity
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get tableName
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set auditEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\AuditEntity $auditEntity
     *
     * @return TargetEntity
     */
    public function setAuditEntity(\ZF\Doctrine\Audit\Entity\AuditEntity $auditEntity)
    {
        $this->auditEntity = $auditEntity;

        return $this;
    }

    /**
     * Get auditEntity
     *
     * @return \ZF\Doctrine\Audit\Entity\AuditEntity
     */
    public function getAuditEntity()
    {
        return $this->auditEntity;
    }

    /**
     * Add revisionEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity
     *
     * @return TargetEntity
     */
    public function addRevisionEntity(\ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity)
    {
        $this->revisionEntity[] = $revisionEntity;

        return $this;
    }

    /**
     * Remove revisionEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity
     */
    public function removeRevisionEntity(\ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity)
    {
        $this->revisionEntity->removeElement($revisionEntity);
    }

    /**
     * Get revisionEntity
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRevisionEntity()
    {
        return $this->revisionEntity;
    }

    /**
     * Add identifier
     *
     * @param \ZF\Doctrine\Audit\Entity\Identifier $identifier
     *
     * @return TargetEntity
     */
    public function addIdentifier(\ZF\Doctrine\Audit\Entity\Identifier $identifier)
    {
        $this->identifier[] = $identifier;

        return $this;
    }

    /**
     * Remove identifier
     *
     * @param \ZF\Doctrine\Audit\Entity\Identifier $identifier
     */
    public function removeIdentifier(\ZF\Doctrine\Audit\Entity\Identifier $identifier)
    {
        $this->identifier->removeElement($identifier);
    }

    /**
     * Get identifier
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}

