<?php

namespace ZF\Doctrine\Audit\Persistence;

use Interop\Container\ContainerInterface;

class RevisionCommentInitializer
{
    public function __invoke(ContainerInterface $container, $instance)
    {
        if ($instance instanceof RevisionCommentAwareInterface) {
            $instance->setRevisionComment(
                $container->get('ZF\Doctrine\Audit\Service\RevisionComment')
            );
        }
    }
}
