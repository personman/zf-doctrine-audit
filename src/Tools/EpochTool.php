<?php

namespace ZF\Doctrine\Audit\Tools;

use Zend\View\Renderer\RendererInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\AuditOptions;

final class EpochTool implements
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    private $viewRenderer;

    public function __construct(
        ObjectManager $objectManager,
        ObjectManager $auditObjectManager,
        AuditOptions $auditOptions,
        RendererInterface $viewRenderer
    ) {
        $this->setObjectManager($objectManager);
        $this->setAuditObjectManager($auditObjectManager);
        $this->setAuditOptions($auditOptions);
        $this->setViewRenderer($viewRenderer);
    }

    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }

    public function setViewRenderer(RendererInterface $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }

    public function generate()
    {
        switch($this->getObjectManager()->getConnection()->getDatabasePlatform()->getName()) {
            case 'mysql':
                $generator = new Generator\Epoch\MySQL(
                    $this->getObjectManager(),
                    $this->getAuditObjectManager(),
                    $this->getAuditOptions(),
                    $this->getViewRenderer()
                );
                break;
            default:
                throw new Exception("Unsupported database platform: "
                    . $this->getObjectManager()->getConnection()->getDatabasePlatform()->getName());
                break;
        }

        return $generator->generate();
    }
}