<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * RevisionEntityIdentifierValue
 */
class RevisionEntityIdentifierValue
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \ZF\Doctrine\Audit\Entity\Identifier
     */
    private $identifier;

    /**
     * @var \ZF\Doctrine\Audit\Entity\RevisionEntity
     */
    private $revisionEntity;


    /**
     * Set value
     *
     * @param string $value
     *
     * @return RevisionEntityIdentifierValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * Set identifier
     *
     * @param \ZF\Doctrine\Audit\Entity\Identifier $identifier
     *
     * @return RevisionEntityIdentifierValue
     */
    public function setIdentifier(\ZF\Doctrine\Audit\Entity\Identifier $identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier
     *
     * @return \ZF\Doctrine\Audit\Entity\Identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set revisionEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity
     *
     * @return RevisionEntityIdentifierValue
     */
    public function setRevisionEntity(\ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity)
    {
        $this->revisionEntity = $revisionEntity;

        return $this;
    }

    /**
     * Get revisionEntity
     *
     * @return \ZF\Doctrine\Audit\Entity\RevisionEntity
     */
    public function getRevisionEntity()
    {
        return $this->revisionEntity;
    }
}

