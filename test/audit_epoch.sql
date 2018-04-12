SELECT 'Begin Artist offset 0 rows 200' as '', now() as '';

DROP PROCEDURE IF EXISTS zf_doctrine_audit_epoch_Artist;

DELIMITER ;;
CREATE PROCEDURE zf_doctrine_audit_epoch_Artist()
BEGIN
    DECLARE done INT DEFAULT FALSE;

    DECLARE var_revision_entity_id bigint(20);

    DECLARE var_id int(11);
    DECLARE var_name varchar(255);

    DECLARE cur CURSOR FOR SELECT
        `id`,
        `name`
        FROM test.Artist        LIMIT 0, 200;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    START TRANSACTION;
    OPEN cur;
    read_loop: LOOP
        FETCH cur INTO
            var_id,
            var_name;

        IF done THEN
            LEAVE read_loop;
        END IF;

        SET var_revision_entity_id = test.get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Artist', 'epoch');

        INSERT INTO `Artist_audit` (
            `id`,
            `name`,
            `revisionEntity_id`
        ) SELECT
            var_id,
convert(var_name using utf8),
            var_revision_entity_id;

    END LOOP read_loop;
    CLOSE cur;
    COMMIT;
END;;

DELIMITER ;
CALL zf_doctrine_audit_epoch_Artist();
SELECT test.close_revision_audit(0, '', '', 'Epoch Artist') into @dummy;

DROP PROCEDURE IF EXISTS zf_doctrine_audit_epoch_Artist;
