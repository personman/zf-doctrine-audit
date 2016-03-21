<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * FieldRevision
 */
class FieldRevision
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\Doctrine\Audit\Entity\FieldStatus
     */
    private $fieldStatus;

    /**
     * @var \ZF\Doctrine\Audit\Entity\Field
     */
    private $field;

    /**
     * @var \ZF\Doctrine\Audit\Entity\Revision
     */
    private $revision;


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
     * Set fieldStatus
     *
     * @param \ZF\Doctrine\Audit\Entity\FieldStatus $fieldStatus
     *
     * @return FieldRevision
     */
    public function setFieldStatus(\ZF\Doctrine\Audit\Entity\FieldStatus $fieldStatus = null)
    {
        $this->fieldStatus = $fieldStatus;

        return $this;
    }

    /**
     * Get fieldStatus
     *
     * @return \ZF\Doctrine\Audit\Entity\FieldStatus
     */
    public function getFieldStatus()
    {
        return $this->fieldStatus;
    }

    /**
     * Set field
     *
     * @param \ZF\Doctrine\Audit\Entity\Field $field
     *
     * @return FieldRevision
     */
    public function setField(\ZF\Doctrine\Audit\Entity\Field $field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return \ZF\Doctrine\Audit\Entity\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set revision
     *
     * @param \ZF\Doctrine\Audit\Entity\Revision $revision
     *
     * @return FieldRevision
     */
    public function setRevision(\ZF\Doctrine\Audit\Entity\Revision $revision = null)
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
}
