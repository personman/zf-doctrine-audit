<?php

namespace ZF\Doctrine\Audit\Tools\Generator\Epoch;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Persistence\ObjectManager;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\AuditOptions;
use ZF\Doctrine\Audit\Tools\Generator\GeneratorInterface;

final class MySQL implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface,
    GeneratorInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public $viewRenderer;

    public function getViewRenderer()
    {
        return $this->viewRenderer;
    }

    public function setViewRenderer(RendererInterface $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }

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

    public function generate()
    {
        $output = '';

        $targetEntities = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
            ->findAll();

        $connection = $this->getObjectManager()->getConnection();

        foreach ($targetEntities as $targetEntity) {
            // We have to iterate the whole stored procedure based on import limit size
            // because mysql cursors don't flex that way.
            $sql = 'SELECT count(*) as ct FROM `' . $targetEntity->getTableName() . '`;' . "\n";
            $count = $this->getObjectManager()->getConnection()->query($sql)->fetch()['ct'];

            $iterations = ceil($count / $this->getAuditOptions()->getEpochImportLimit());

            $columnDefinitionSql = "
                SELECT column_name, column_type
                FROM information_schema.columns
                WHERE table_schema = '"
                . $connection->getDatabase()
                . "' and table_name = '"
                . $targetEntity->getTableName()
                . "'"
                ;
            $tableColumns = $connection->fetchAll($columnDefinitionSql);
            $column = [];
            foreach ($tableColumns as $column) {
                $columns[$column['column_name']] = $column['column_type'];
            }

            $offset = 0;
            for ($i = 0; $i < $iterations; $i++) {
                $viewParams = [
                    'offset' => $offset,
                    'limit' => $this->getAuditOptions()->getEpochImportLimit(),
                    'columns' => $columns,
                    'targetEntity' => $targetEntity,
                    'targetDatabase' => $connection->getDatabase(),
                ];

                $viewModel = new ViewModel($viewParams);
                $viewModel->setTemplate('zf-doctrine-audit/epoch/mysql');

                $output .= $this->getViewRenderer()->render($viewModel);

                $offset += $this->getAuditOptions()->getEpochImportLimit();
            }

            $offset = 0;
            $columns = [];
        }

        return $output;
    }
}
