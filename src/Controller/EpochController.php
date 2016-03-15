<?php

namespace ZF\Doctrine\Audit\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Prompt;
use Zend\ProgressBar\Adapter\Console as ProgressBar;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Tools\SchemaTool;
use ZF\Doctrine\Audit\Persistence;
use ZF\Doctrine\Audit\Entity;
use RuntimeException;
use DateTime;

class EpochController extends AbstractActionController implements
    Persistence\AuditEntitiesAwareInterface,
    Persistence\AuditObjectManagerAwareInterface,
    Persistence\ObjectManagerAwareInterface,
    Persistence\AuditOptionsAwareInterface,
{
    use Persistence\AuditEntitiesAwareTrait;
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;
    use Persistence\AuditOptionsAwareTrait;

    public function indexAction()
    {
        $console = $this->getServiceLocator()->get('console');

        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException('You can only use this action from a console.');
        }

        $console->write(
            "********************************************************************************\n",
            Color::GREEN
        );
        $console->write(
            "* This epoch tool will populate the zf-apigility-audit auditing tables with a  *\n",
            Color::GREEN
        );
        $console->write(
            "* snapshot of the database as it exists at this point in time.  The audit      *\n",
            Color::GREEN
        );
        $console->write(
            "* tables must be empty when this tool is ran.  In order to retire a set of     *\n",
            Color::GREEN
        );
        $console->write(
            "* audit tables you may continue the audit trail by setting the auto_increment  *\n",
            Color::GREEN
        );
        $console->write(
            "* values of the Revision and RevisionEntity tables to the next id in series    *\n",
            Color::GREEN
        );
        $console->write(
            "********************************************************************************\n",
            Color::GREEN
        );

        if (! Prompt\Confirm::prompt('Continue? y/n ')) {
            return;
        }

        $revisionNumber = 0;
        foreach ($this->getAuditEntities() as $className => $classInfo) {

            $queryBuilder = $this->getObjectManager()->createQueryBuilder();
            $queryBuilder->select('row')
                ->from($className, 'row')
                ;

            $paginator = new Paginator(
                $queryBuilder->getQuery()
                    ->setFirstResult(0)
                    ->setMaxResults($this->getAuditOptions()['epoch_import_limit'])
            );

            $paginatorCount = count($paginator);

            if ($paginatorCount) {
                $progressBar = new ProgressBar($console);
                $console->write($className . " ", Color::YELLOW);
                $console->write($paginatorCount . ' records' . "\n", Color::RED);
                $timeStart = new DateTime();
                $totalCount = 0;
            }

            $auditEntityClass = 'ZF\\Doctrine\\Audit\\Entity\\' . str_replace('\\', '_', $className);

            $revisionNumber ++;

            $start = 0;
            $dataCount = 0;
            while (true) {
                foreach ($paginator as $entity) {
                    if (! isset($revision) || ! $revision) {
                        $revision = new Entity\Revision();
                        $revision->setTimestamp(new DateTime());
                        $revision->setComment('Epoch');
                        $this->getAuditObjectManager()->persist($revision);
                    } else {
                        $revision = $this->getAuditObjectManager()->merge($revision);
                    }

                    $revisionEntity = new Entity\RevisionEntity();
                    $revisionEntity->setRevision($revision);

                    $revisionEntity->setRevisionType('EPO');
                    $revisionEntity->setAuditEntityClass($auditEntityClass);
                    $revisionEntity->setTargetEntityClass($className);
                    $revisionEntity->setEntityKeys(
                        $this->getAuditService()->getEntityIdentifierValues($entity)
                    );

                    if (method_exists($entity, '__toString')) {
                        $revisionEntity->setTitle((string) $entity);
                    }

                    $auditEntity = new $auditEntityClass();

                    $this->getAuditService()
                        ->hydrateAuditEntityFromTargetEntity($auditEntity, $entity);
                    $auditEntity->setRevisionEntity($revisionEntity);

                    $this->getAuditObjectManager()->persist($revisionEntity);
                    $this->getAuditObjectManager()->persist($auditEntity);

                    $dataCount ++;
                    $totalCount ++;
                }

                if (! $dataCount) {
                    unset($revision);
                    break;
                } else {
                    $this->getAuditObjectManager()->flush();
                    $this->getAuditObjectManager()->detach($revision);
                    $this->getAuditObjectManager()->clear();
                    $this->getObjectManager()->clear();
                }
                $progressBar->notify(
                    $totalCount,
                    $paginatorCount,
                    round($totalCount / $paginatorCount, 2),
                    null,
                    null,
                    null
                );

                $dataCount = 0;

                $start += $this->getAuditOptions()['epoch_import_limit'];
                $paginator->getQuery()->setFirstResult($start);
            }

            $progressBar->finish();
        }

        $console->write("\nEpoch is complete\n", Color::CYAN);
    }
}
