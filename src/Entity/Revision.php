<?php

namespace ZF\Doctrine\Audit\Entity;

/**
 * Revision
 */
class Revision
{
    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var integer
     */
    private $userId;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $revisionEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $fieldRevision;

    private $connectionId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->revisionEntity = new \Doctrine\Common\Collections\ArrayCollection();
        $this->fieldRevision = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getConnectionId()
    {
        return $this->connectionId;
    }

    public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;

        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Revision
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Revision
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Revision
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
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
     * @return Revision
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
     * Add fieldRevision
     *
     * @param \ZF\Doctrine\Audit\Entity\FieldRevision $fieldRevision
     *
     * @return Revision
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
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $userEmail;


    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return Revision
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userEmail
     *
     * @param string $userEmail
     *
     * @return Revision
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;

        return $this;
    }

    /**
     * Get userEmail
     *
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }
}
