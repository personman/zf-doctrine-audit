<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * RevisionEntity
 */
class RevisionEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\Doctrine\Audit\Entity\Revision
     */
    private $revision;

    /**
     * @var \ZF\Doctrine\Audit\Entity\TargetEntity
     */
    private $targetEntity;

    /**
     * @var \ZF\Doctrine\Audit\Entity\RevisionType
     */
    private $revisionType;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Set revision
     *
     * @param \ZF\Doctrine\Audit\Entity\Revision $revision
     *
     * @return RevisionEntity
     */
    public function setRevision(\ZF\Doctrine\Audit\Entity\Revision $revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision
     *
     * @return \ZF\Doctrine\Audit\Entity\Revision
     */
    public function getRevision()
    {
        return $this->revision;
    }

    /**
     * Set targetEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity
     *
     * @return RevisionEntity
     */
    public function setTargetEntity(\ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity)
    {
        $this->targetEntity = $targetEntity;

        return $this;
    }

    /**
     * Get targetEntity
     *
     * @return \ZF\Doctrine\Audit\Entity\TargetEntity
     */
    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    /**
     * Set revisionType
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionType $revisionType
     *
     * @return RevisionEntity
     */
    public function setRevisionType(\ZF\Doctrine\Audit\Entity\RevisionType $revisionType)
    {
        $this->revisionType = $revisionType;

        return $this;
    }

    /**
     * Get revisionType
     *
     * @return \ZF\Doctrine\Audit\Entity\RevisionType
     */
    public function getRevisionType()
    {
        return $this->revisionType;
    }
}

