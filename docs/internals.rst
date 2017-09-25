Internals
=========

This document describes how auditing works in the code, internal to the application.

There are four primary actions which zf-doctrine-audit implements.  The first is building an audit database based on the 
configuration listing entities and joinEntities.  


Audit Entity Autoloader 
-----------------------

In order to map configured entities to a database we must have a class for each audited entity.  The naming of these classes
follows this pattern (found in ``ZF\Doctrine\Audit\Repository\AuditEntityRepository``)::

  return "ZF\\Doctrine\\Audit\\RevisionEntity\\" . str_replace('\\', '_', $entityName);

So an entity in the target object manager named ``Db\Entity\User`` will be audited by an entity in the audit database named 
``ZF\\Doctrine\\Audit\\RevisionEntity\\Db_Entity_User``.  Access to this entity through the audit object manager works as you
would expect in a doctrine object manager::

  $auditObjectManger->getRepository('ZF\\Doctrine\\Audit\\RevisionEntity\\Db_Entity_User')
      ->findBy([
          'id' => 2,
      ]);

This code will fetch the complete audit history for the ``Db\Entity\User`` entity with id = 2.

This is possible because audit entity classes are dynamically created via an Autoloader.  


Audit Object Manager Metadata
-----------------------------

Mapping drivers for configured ``entities`` and ``joinEntities`` dynamically create metadata based on target entity metadata.
By introspecting the existing target entity metadata a new metadata definition can be assigned to an Autoloader created class.

The dynamically created classes assigned to dynamically created metadata combined with static auditing entities creates a complete
audit object manager in the application.  This object manager can be used by the schema tool and that is how the audit database is created.


