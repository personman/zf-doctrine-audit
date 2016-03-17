<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use DoctrineDataFixtureModule\Loader\ServiceLocatorAwareLoader;
use RuntimeException;
use Doctrine\ORM\Tools\SchemaTool;
use ZF\Doctrine\Audit\Persistence;

class EpochMySQLController extends AbstractActionController implements
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function importAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $targetEntities = $this->getAuditObjectManager()
            ->getRepository('ZF\Doctrine\Audit\Entity\TargetEntity')
            ->findAll();

        $connection = $this->getObjectManager()->getConnection();

        foreach ($targetEntities as $targetEntity) {
#            if ($targetEntity->getTableName() != 'Weather') continue;
            // We have to iterate the whole stored procedure based on import limit size
            // because mysql cursors don't flex that way.
#            print_r(get_class_methods($connection));die();
            $queryBuilder = $this->getObjectManager()->createQueryBuilder();
            $queryBuilder->select('count(ct)');
            $queryBuilder->from($targetEntity->getName(), 'ct');

            $count = $queryBuilder->getQuery()->getSingleScalarResult();
            $iterations = ceil($count / $this->getAuditOptions()['epoch_import_limit']);

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

            $viewRender = $this->getServiceLocator()->get('ViewRenderer');

            $offset = 1;
            for ($i = 0; $i < $iterations; $i++) {
                $viewParams = [
                    'offset' => $offset,
                    'limit' => $this->getAuditOptions()['epoch_import_limit'],
                    'columns' => $columns,
                    'targetEntity' => $targetEntity,
                    'targetDatabase' => $connection->getDatabase(),
                ];

                $viewModel = new ViewModel($viewParams);
                $viewModel->setTemplate('zf-doctrine-audit/epoch/mysql');

                echo($viewRender->render($viewModel));

                $offset += $this->getAuditOptions()['epoch_import_limit'];
            }

            $offset = 1;
            $columns = [];
        }
        die();
    }
}

/*
call epoch_User_Type();
*/
