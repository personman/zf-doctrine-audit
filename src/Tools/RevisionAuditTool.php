<?php

namespace ZF\Doctrine\Audit\Tools;

use Zend\View\Renderer\RendererInterface;
use Zend\Authentication\AuthenticationService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Query\ResultSetMapping;
use ZF\OAuth2\Doctrine\Identity\AuthenticatedIdentity as OAuth2AuthenticatedIdentity;
use ZF\MvcAuth\Identity\AuthenticatedIdentity;
use ZF\MvcAuth\Identity\GuestIdentity;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\AuditOptions;
use ZF\Doctrine\Audit\RevisionComment;

final class RevisionAuditTool implements
    Persistence\ObjectManagerAwareInterface,
    Persistence\RevisionCommentAwareInterface
{
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\RevisionCommentAwareTrait;

    private $authenticationService;

    public function __construct(
        ObjectManager $objectManager,
        RevisionComment $revisionComment,
        AuthenticationService $authenticationService = null
    ) {
        $this->setObjectManager($objectManager);
        $this->setRevisionComment($revisionComment);
        $this->authenticationService = $authenticationService;
    }

    /**
     * Close a revision - can be done manually or automatically via postFlush
     */
    public function close()
    {
        $userId = 0;
        $userName = 'guest';
        $userEmail = '';

        if ($this->authenticationService) {
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
        }

        $query = $this->getObjectManager()
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

        $query->getResult();

        // Reset the revision comment
        $this->revisionComment->setComment('');
    }
}
