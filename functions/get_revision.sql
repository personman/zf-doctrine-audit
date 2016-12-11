DELIMITER //

DROP FUNCTION IF EXISTS get_revision__zf_doctrine_audit;//
TRUNCATE TABLE Revision_Audit;//

CREATE FUNCTION get_revision__zf_doctrine_audit()
    RETURNS bigint
    READS SQL DATA
    BEGIN

    DECLARE revisionId BIGINT DEFAULT 0;
    SELECT id INTO revisionId FROM Revision_Audit WHERE connectionId = CONNECTION_ID() LIMIT 1;

    IF revisionId = 0 THEN
        INSERT INTO Revision_Audit (
            createdAt,
            connectionId
        ) VALUES (
            now(), CONNECTION_ID()
        );

        SET revisionId = LAST_INSERT_ID();
    END IF;

    RETURN revisionId;
END;//

DELIMITER ;

select get_revision__zf_doctrine_audit();