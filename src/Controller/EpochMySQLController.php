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
    Persistence\ObjectManagerAwareInterface
{
    use Persistence\AuditObjectManagerAwareTrait;
    use Persistence\ObjectManagerAwareTrait;

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

        foreach ($targetEntities as $targetEntity) {
            $storedProcedure = <<<EOF
DELIMITER ;;
DROP PROCEDURE IF EXISTS zf_doctrine_audit_epoch_{$targetEntity->getTableName()};;

CREATE PROCEDURE zf_doctrine_audit_epoch_{$targetEntity->getTableName()}()
BEGIN
    DECLARE var_revision_id bigint(20);
    DECLARE var_revision_entity_id bigint(20);
    DECLARE var_revision_type bigint(20);
    DECLARE var_target_entity bigint(20);
    DECLARE done INT DEFAULT FALSE;

EOF;

            $connection = $this->getObjectManager()->getConnection();

            $columnDefinitionSql = "
                SELECT
                    column_name,
                    column_type
                FROM information_schema.columns
                WHERE table_schema = '"
                . $connection->getDatabase()
                . "' and table_name = '"
                . $targetEntity->getTableName()
                . "'";

            $tableColumns = $connection->fetchAll($columnDefinitionSql);
            $columnNames = [];
            foreach ($tableColumns as $column) {
                $columnNames[$column['column_name']] = 'var_' . strtolower($column['column_name']);
                $storedProcedure .= "    DECLARE " . $columnNames[$column['column_name']];
                $storedProcedure .= ' ' . $column['column_type'];
                $storedProcedure .= ";\n";
            }

            $storedProcedure .= "\n    DECLARE rows CURSOR FOR\n        SELECT\n            "
                . implode(", \n            ", array_keys($columnNames))
                . "\n        FROM "
                . $connection->getDatabase()
                . '.'
                . $targetEntity->getTableName()
                . ";\n\n"
                . "    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;"
                . "\n\n"
                . "    SET var_target_entity = " . $targetEntity->getId() . ';'
                ;

            $storedProcedure .= <<<EOF

    SET var_revision_type = 4;

    INSERT INTO Revision_Audit (
        comment,
        createdAt
    ) VALUES (
        'Epoch',
        now()
    );

    SELECT last_insert_id() INTO var_revision_id;

    OPEN rows;
    read_loop: LOOP

        FETCH next FROM rows INTO

EOF;

            $storedProcedure .= '            ';
            $storedProcedure .= implode(",\n            ", $columnNames) . ";";

            $storedProcedure .= <<<EOF


        IF DONE then
            LEAVE read_loop;
        END IF;

        INSERT INTO RevisionEntity_Audit (
            revision_id,
            target_entity_id,
            revision_type_id,
            title
        ) values (
            var_revision_id,
            var_target_entity,
            var_revision_type,
            'Epoch'
        );

        SELECT last_insert_id() INTO var_revision_entity_id;

EOF;

            $revisionEntityColumns = $columnNames;
            $revisionEntityColumns['revisionEntity_id'] = 'var_revision_entity_id';

            $storedProcedure .=
        "\n        INSERT INTO "
        . $targetEntity->getAuditEntity()->getTableName()
        . " (\n            "
        . implode(",\n            ", array_keys($revisionEntityColumns))
        . "\n        ) SELECT\n            "
        . implode(",\n            ", $revisionEntityColumns)
        . ";\n\n"
        ;

            foreach ($targetEntity->getIdentifier() as $identifier) {
                $storedProcedure .= "
                INSERT INTO RevisionEntityIdentifierValue_Audit (
                    value,
                    identifier_id,
                    revision_entity_id
                ) VALUES (\n            "
                        . $revisionEntityColumns[$identifier->getColumnName()]
                        . ",\n            "
                        . $identifier->getId()
                        . ",\n            "
                        . 'var_revision_entity_id'
                        . "\n        );\n\n";
            }

            $storedProcedure .= "    END LOOP;
    CLOSE rows;
END;;

DELIMITER ;";

            $storedProcedure .= <<<EOF


CALL zf_doctrine_audit_epoch_{$targetEntity->getTableName()}();
DROP PROCEDURE zf_doctrine_audit_epoch_{$targetEntity->getTableName()};


EOF;

            print_r($storedProcedure);
        }
    }
}

/*
call epoch_User_Type();
*/