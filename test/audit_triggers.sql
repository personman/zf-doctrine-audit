
DELIMITER //

DROP FUNCTION IF EXISTS close_revision_audit;//
CREATE FUNCTION close_revision_audit(
    p_userId BIGINT,
    p_userName varchar(255),
    p_userEmail varchar(255),
    p_comment text
) RETURNS boolean
BEGIN

    UPDATE audit.Revision_Audit SET
        connectionId = null,
        userId = p_userId,
        userName = p_userName,
        userEmail = p_userEmail,
        comment = p_comment
    WHERE connectionId = CONNECTION_ID();

    RETURN true;
END;//

DROP FUNCTION IF EXISTS get_revision_entity_audit;//
CREATE FUNCTION get_revision_entity_audit(
    p_targetEntity varchar(255) charset utf8 collate utf8_unicode_ci,
    p_revisionType varchar(255) charset utf8 collate utf8_unicode_ci
)
    RETURNS bigint
    READS SQL DATA
BEGIN

    DECLARE revisionTypeId BIGINT DEFAULT 0;
    DECLARE revisionId BIGINT DEFAULT 0;
    DECLARE targetEntityId BIGINT DEFAULT 0;

    SELECT id INTO revisionTypeId
    FROM audit.RevisionType_Audit
    WHERE name = p_revisionType
    LIMIT 1;

    SELECT id INTO revisionId
    FROM audit.Revision_Audit
    WHERE connectionId = CONNECTION_ID()
    LIMIT 1;

    IF revisionId = 0 THEN
        INSERT INTO audit.Revision_Audit (
            createdAt,
            connectionId
        ) VALUES (
            now(), CONNECTION_ID()
        );

        SET revisionId = LAST_INSERT_ID();
    END IF;

    SELECT id INTO targetEntityId
    FROM audit.TargetEntity_Audit
    WHERE name = p_targetEntity;

    INSERT INTO audit.RevisionEntity_Audit (
        revision_id,
        target_entity_id,
        revision_type_id
    ) VALUES (
        revisionId,
        targetEntityId,
        revisionTypeId
    );

    RETURN LAST_INSERT_ID();
END;//

DROP TRIGGER IF EXISTS Artist_insert_audit;//
CREATE TRIGGER Artist_insert_audit
AFTER INSERT ON Artist
FOR EACH ROW
BEGIN

INSERT INTO audit.Artist_audit (`name`, `id`, revisionEntity_id) VALUES (NEW.name, NEW.id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Artist', 'insert')
);
END;//

DROP TRIGGER IF EXISTS Artist_update_audit;//
CREATE TRIGGER Artist_update_audit
AFTER UPDATE ON Artist
FOR EACH ROW
BEGIN

INSERT INTO audit.Artist_audit (`name`, `id`, revisionEntity_id) VALUES (NEW.name, NEW.id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Artist', 'update')
);
END;//

DROP TRIGGER IF EXISTS Artist_delete_audit;//
CREATE TRIGGER Artist_delete_audit
AFTER DELETE ON Artist
FOR EACH ROW
BEGIN

INSERT INTO audit.Artist_audit (`name`, `id`, revisionEntity_id) VALUES (OLD.name, OLD.id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Artist', 'delete')
);
END;//

DROP TRIGGER IF EXISTS Album_insert_audit;//
CREATE TRIGGER Album_insert_audit
AFTER INSERT ON Album
FOR EACH ROW
BEGIN

INSERT INTO audit.Album_audit (`name`, `id`, `artist_id`, revisionEntity_id) VALUES (NEW.name, NEW.id, NEW.artist_id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Album', 'insert')
);
END;//

DROP TRIGGER IF EXISTS Album_update_audit;//
CREATE TRIGGER Album_update_audit
AFTER UPDATE ON Album
FOR EACH ROW
BEGIN

INSERT INTO audit.Album_audit (`name`, `id`, `artist_id`, revisionEntity_id) VALUES (NEW.name, NEW.id, NEW.artist_id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Album', 'update')
);
END;//

DROP TRIGGER IF EXISTS Album_delete_audit;//
CREATE TRIGGER Album_delete_audit
AFTER DELETE ON Album
FOR EACH ROW
BEGIN

INSERT INTO audit.Album_audit (`name`, `id`, `artist_id`, revisionEntity_id) VALUES (OLD.name, OLD.id, OLD.artist_id, get_revision_entity_audit('ZFTest\\Doctrine\\Audit\\Entity\\Album', 'delete')
);
END;//

DELIMITER ;
