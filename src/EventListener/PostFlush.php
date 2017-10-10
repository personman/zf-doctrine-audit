<?php

namespace ZF\Doctrine\Audit\EventListener;

use Zend\Authentication\AuthenticationService;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;
use ZF\OAuth2\Doctrine\Identity\AuthenticatedIdentity as OAuth2AuthenticatedIdentity;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;
use ZF\MvcAuth\Identity\GuestIdentity;
use ZF\Doctrine\Audit\RevisionComment;

/**
 * After each change to the database the revision entity stays
 * "open" and mapped to the mysql connection.  This listener
 * must run after every flush() event to close the revision on
 * the current connection thereby completeing the auditing transaction.
 *
 * This class may be overridden via the service_manager
 * in order to implement your custom identity for revision
 * auditing.  You will still need to use native query
 * because doctrine createQuery expects a FROM clause.
 */
final class PostFlush
{
    private $authenticationService;
    private $revisionComment;
    private $enable = true;

    public function __construct(RevisionComment $revisionComment, AuthenticationService $authenticationService = null)
    {
        $this->revisionComment = $revisionComment;
        $this->authenticationService = $authenticationService;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $userId = 0;
        $userName = 'guest';
        $userEmail = '';

        if ($this->authenticationService->getIdentity() instanceof OAuth2AuthenticatedIdentity) {
            $user = $this->authenticationService->getIdentity()->getUser();

            if (method_exists($user, 'getId')) {
                $userId = $user->getId();
            }

            if (method_exists($user, 'getDisplayName')) {
                $userName = $user->getDisplayName();
            }

            if (method_exists($user, 'getEmail')) {
                $userEmail = $user->getEmail();
            }
        } elseif ($this->authenticationService->getIdentity() instanceof AuthenticatedIdentity) {
            $userId = $this->authenticationService->getIdentity()->getAuthenticationIdentity()['user_id'];
            $userName = $this->authenticationService->getIdentity()->getName();
        } elseif ($this->authenticationService->getIdentity() instanceof GuestIdentity) {
        } else {
            // Is null or other identity
        }

        $query = $args->getEntityManager()
            ->createNativeQuery(
                "
                SELECT close_revision_audit(:userId, :userName, :userEmail, :comment)
            ",
                new ResultSetMapping()
            )
            ->setParameter('userId', $userId)
            ->setParameter('userName', $userName)
            ->setParameter('userEmail', $userEmail)
            ->setParameter('comment', $this->revisionComment->getComment());
        ;

        if ($this->enable) {
            $query->getResult();
        }

        // Reset the revision comment
        $this->revisionComment->setComment('');
    }

    public function enable()
    {
        $this->enable = true;
    }

    public function disable()
    {
        $this->enable = false;
    }
}
