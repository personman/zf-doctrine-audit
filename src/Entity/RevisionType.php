<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * RevisionType
 */
class RevisionType
{
    /**
     * @var string
     */
    private $revisionType;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revisionEntity;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->revisionEntity = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set revisionType
     *
     * @param string $revisionType
     *
     * @return RevisionType
     */
    public function setRevisionType($revisionType)
    {
        $this->revisionType = $revisionType;

        return $this;
    }

    /**
     * Get revisionType
     *
     * @return string
     */
    public function getRevisionType()
    {
        return $this->revisionType;
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
     * Add revisionEntity
     *
     * @param \ZF\Doctrine\Audit\Entity\RevisionEntity $revisionEntity
     *
     * @return RevisionType
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
     * @var string
     */
    private $name;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return RevisionType
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
}
