<?php

namespace ZF\Doctrine\Audit\Controller\Epoch;

use RuntimeException;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Doctrine\ORM\Tools\SchemaTool;
use ZF\Doctrine\Audit\Persistence;
use Doctrine\ORM\Query\ResultSetMapping;

final class MySQLController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public $viewRenderer;

    public function importAction()
    {
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $targetEntities = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
            ->findAll();

        $connection = $this->getObjectManager()->getConnection();

        foreach ($targetEntities as $targetEntity) {
            // We have to iterate the whole stored procedure based on import limit size
            // because mysql cursors don't flex that way.
            $sql = 'SELECT count(*) as ct FROM `' . $targetEntity->getTableName() . '` t';
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

            $offset = 1;
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

                echo($this->viewRenderer->render($viewModel));

                $offset += $this->getAuditOptions()->getEpochImportLimit();
            }

            $offset = 1;
            $columns = [];
        }
        die();
    }
}