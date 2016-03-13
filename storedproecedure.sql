DECLARE @id bigint(20);
DECLARE @name varchar(255);
DECLARE @description varchar(255);
DECLARE @deleted tinyint(1);
DECLARE @datecreated datetime;
DECLARE @datemodified datetime;
DECLARE @createdby bigint(20);
DECLARE @modifiedby bigint(20);

DECLARE @rows CURSOR fast_forward FOR
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

OPEN @rows
WHILE 1 = 1
BEGIN
    FETCH next
        FROM @rows
        INTO
            @id,
            @name,
            @description,
            @deleted,
            @datecreated,
            @datemodified,
            @createdby,
            @modifiedby
    ;

    IF @@fetch_status <> 0
    BEGIN
        break
    END

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
    ) VALUES (
        @id,
        @name,
        @description,
        @deleted,
        @datecreated,
        @datemodified,
        @createdby,
        @modifiedby,
        last_insert_id()
    );
END

CLOSE @rows
DEALLOCATE @rows

CREATE PROCEDURE cursor_ROWPERROW()
BEGIN
  DECLARE cursor_ID INT;
  DECLARE cursor_VAL VARCHAR;
  DECLARE done INT DEFAULT FALSE;
  DECLARE cursor_i CURSOR FOR SELECT * FROM User_Type;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
  OPEN cursor_i;
  read_loop: LOOP
    FETCH cursor_i INTO cursor_ID, cursor_VAL;
    IF done THEN
      LEAVE read_loop;
    END IF;
    INSERT INTO table_B(ID, VAL) VALUES(cursor_ID, cursor_VAL);
  END LOOP;
  CLOSE cursor_i;
END;
;;
