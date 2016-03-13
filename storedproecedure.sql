DELIMITER ;;
DROP PROCEDURE IF EXISTS epoch_User_Type;;
CREATE PROCEDURE epoch_User_Type()
BEGIN
    DECLARE @id;;
    DECLARE @name;;
    SET @description;;
    SET @deleted;;
    SET @datecreated;
    SET @datemodified;
    SET @createdby;
    SET @modifiedby;

    DECLARE done INT DEFAULT FALSE;

    DECLARE rows CURSOR FOR
        SELECT
        Id,
        Name,
        Description,
        Deleted,
        DateCreated,
        DateModified,
        CreatedBy,
        ModifiedBy
        FROM new.User_Type;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

OPEN rows;
read_loop: LOOP
    FETCH next
        FROM rows
        INTO
            id,
            name,
            description,
            deleted,
            datecreated,
            datemodified,
            createdby,
            modifiedby
    ;

    INSERT INTO RevisionEntity (
        revision_id
    ) values (
        1
    );

    INSERT INTO User_Type (
        Id,
        Name,
        Description,
        Deleted,
        DateCreated,
        DateModified,
        CreatedBy,
        ModifiedBy,
        revisionEntity_id
    ) SELECT
        @id,
        @name,
        @description,
        @deleted,
        @datecreated,
        @datemodified,
        @createdby,
        @modifiedby,
        last_insert_id()
    ;

    IF DONE then 
       LEAVE read_loop;
    END IF;
END LOOP;

CLOSE rows;
END;;

DELIMITER ;

