<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * FieldStatus
 */
class FieldStatus
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fieldRevision;

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
     * @return FieldStatus
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
     * @return FieldStatus
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
}
