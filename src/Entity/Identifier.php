<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * Identifier
 */
class Identifier
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revisionEntityIdentifierValue;

    /**
     * @var \ZF\Doctrine\Audit\Entity\TargetEntity
     */
    private $targetEntity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->revisionEntityIdentifierValue = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set fieldName
     *
     * @param string $fieldName
     *
     * @return Identifier
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
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
     * @return Identifier
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
     * Set targetEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity
     *
     * @return Identifier
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
}

