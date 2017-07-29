<?php

namespace ZF\Doctrine\Audit\EventListener;

use Zend\Permissions\Rbac\AbstractRole as AbstractRbacRole;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;
use ZF\OAuth2\Doctrine\Identity\AuthenticatedIdentity as OAuth2AuthenticatedIdentity;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;
use ZF\MvcAuth\Identity\GuestIdentity;
use ZF\Doctrine\Audit\RevisionComment;

final class PostFlush
{
    private $identity;
    private $revisionComment;

    public function __construct(RevisionComment $revisionComment, AbstractRbacRole $identity = null)
    {
        $this->identity = $identity;
        $this->revisionComment = $revisionComment;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $userId = 0;
        $userName = 'guest';
        $userEmail = '';

        if ($this->identity instanceof OAuth2AuthenticatedIdentity) {
            $user = $this->identity->getUser();

            if (method_exists($user, 'getId')) {
                $userId = $user->getId();
            }

            if (method_exists($user, 'getName')) {
                $userName = $user->getName();
            }

            if (method_exists($user, 'getEmail')) {
                $userEmail = $user->getEmail();
            }
        } elseif ($this->identity instanceof AuthenticatedIdentity) {
            $userId = $this->identity->getAuthenticationIdentity()['user_id'];
            $userName = $this->identity->getName();
        } elseif ($this->identity instanceof GuestIdentity) {

        } else {
            // Is null or other identity
        }

        $resultSetMapping = new ResultSetMapping();

        $query = $args->getEntityManager()
            ->createNativeQuery("
                SELECT close_revision_audit(:userId, :userName, :userEmail, :comment)
            ", $resultSetMapping)
            ->setParameter('userId', $userId)
            ->setParameter('userName', $userName)
            ->setParameter('userEmail', $userEmail)
            ->setParameter('comment', $this->revisionComment->getComment())
            ;
        ;

       $query->getResult();

       // Reset the revision comment
       $this->revisionComment->setComment('');
    }
}