<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * RevisionEntity
 */
class RevisionEntity
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revisionEntityIdentifierValue;

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
        $this->revisionEntityIdentifierValue = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return RevisionEntity
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
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
     * Add revisionEntityIdentifierValue
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntityIdentifierValue $revisionEntityIdentifierValue
     *
     * @return RevisionEntity
     */
    public function addRevisionEntityIdentifierValue(\ZF\Doctrine\Audit\Entity\RevisionEntityIdentifierValue $revisionEntityIdentifierValue)
    {
        $this->revisionEntityIdentifierValue[] = $revisionEntityIdentifierValue;

        return $this;
    }

    /**
     * Remove revisionEntityIdentifierValue
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntityIdentifierValue $revisionEntityIdentifierValue
     */
    public function removeRevisionEntityIdentifierValue(\ZF\Doctrine\Audit\Entity\RevisionEntityIdentifierValue $revisionEntityIdentifierValue)
    {
        $this->revisionEntityIdentifierValue->removeElement($revisionEntityIdentifierValue);
    }

    /**
     * Get revisionEntityIdentifierValue
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRevisionEntityIdentifierValue()
    {
        return $this->revisionEntityIdentifierValue;
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

