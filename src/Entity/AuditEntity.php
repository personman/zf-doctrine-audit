<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * AuditEntity
 */
class AuditEntity
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
     * @var \ZF\Doctrine\Audit\Entity\TargetEntity
     */
    private $targetEntity;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return AuditEntity
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
     * @return AuditEntity
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
     * Set targetEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity
     *
     * @return AuditEntity
     */
    public function setTargetEntity(\ZF\Doctrine\Audit\Entity\TargetEntity $targetEntity = null)
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
