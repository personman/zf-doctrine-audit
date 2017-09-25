How Auditing Works
==================

Triggers are created for every database table for every entity and joinEntity configured for zf-doctrine-audit.  
Using triggers allows database modifications to be made anytime a change is made to the database whether through the ORM
or through a console connection, etc.  These triggers are created on the target database.  

When a change happens on the target database to a record configured to be audited an audit record is created.  
Each target table has a RevisionEntity table in the audit database which is a copy of the target table with a new field added
called ``revisionEntity_id``.  This is a foreign key to the RevisionEntity_Audit table.  The RevisionEntity_Audit table works like a receipt
for each set of changes to a row of audited data.  


RevisionEntity Entity
--------------------------

This table is a reciept for the change audited in the audit database.  The RevisionEntity_Audit table is used by the 
``ZF\Doctrine\Audit\Entity\RevisioinEntity`` entity.  This entity has a relationship to ``ZF\Doctrine\Audit\Entity\RevisionType`` 
which defines the type of audit which took place, whether insert, update, delete, or epoch.  

Another relationship to ``ZF\Doctrine\Audit\Entity\TargetEntity`` defines which entity was acted upon.  The ``TargetEntity`` contains 
dynamic data populated by data-fixture.  This ``TargetEntity`` has a relationship to the ``ZF\Doctrine\Audit\Entity\AuditEntity`` which 
has information about the auditing entity.  

Finally the RevisionAudit has a relationship to the ``ZF\Doctrine\Audit\Entity\Revision`` entity.  This ``Revision`` entity groups 
``RevisionEntity`` records together into ORM ``flush();`` operations.  So if your object manager has three entities to persist or update 
when ``flush();`` is called there will be one ``RevisionEntity`` record for each managed entity and one ``Revision`` entity.  This design 
allows groups of related database changes to be saved via the audit record.


Revision Entity
---------------

Automatically populated when created, the ``createdAt`` field stores the time the ``Revision`` was created.  This is the timestamp for a
complete audit record.

There are several other fields which can be populated to help track who made a change and why the chane was made.  
``comment``, ``userId``, ``userName``, and ``userEmail`` can be set through the :doc:`revision_comment` when working with the database 
through the ORM in PHP.  These values will default to empty with a userName of 'not orm' when making changes to the database outside
of PHP such as through a terminal connection.
