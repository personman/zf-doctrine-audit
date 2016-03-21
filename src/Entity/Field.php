<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * Field
 */
class Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $columnName;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fieldRevision;

    /**
     * @var \ZF\Doctrine\Audit\Entity\TargetEntity
     */
    private $targetEntity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fieldRevision = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Field
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
     * Set columnName
     *
     * @param string $columnName
     *
     * @return Field
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get columnName
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
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
     * Add fieldRevision
     *
     * @param \ZF\Doctrine\Audit\Entity\FieldRevision $fieldRevision
     *
     * @return Field
     */
    public function addFieldRevision(\ZF\Doctrine\Audit\Entity\FieldRevision $fieldRevision)
    {
        $this->fieldRevision[] = $fieldRevision;

        return $this;
    }

    /**
     * Remove fieldRevision
     *
     * @param \ZF\Doctrine\Audit\Entity\FieldRevision $fieldRevision
     */
    public function removeFieldRevision(\ZF\Doctrine\Audit\Entity\FieldRevision $fieldRevision)
    {
        $this->fieldRevision->removeElement($fieldRevision);
    }

    /**
     * Get fieldRevision
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFieldRevision()
    {
        return $this->fieldRevision;
    }

    /**
     * Set targetEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity
     *
     * @return Field
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
