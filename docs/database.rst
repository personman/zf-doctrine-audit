Audit Database & Triggers
=========================

With your application configured with a new entity manager for the audit 
database and your configuration containing the entities you want to audit, it's time to create your audit database::

  php public/index.php orm:schema-tool:create --object-manager=doctrine.entitymanager.orm_zf_doctrine_audit

This command will create the database for the given object manager.  Be sure you specify the same object manager as 
the configuration ``audit_object_manager``.

*A note about migrations:  Currently Doctrine doesn't have a way to run migrations for two databases.  
It makes sense to have another set of migrations for the audit database.  This repository does not try to solve this problem
and leaves the use of migrations up to you.*


Run Fixtures
------------

The audit database requires data, fixtures, to operate.  To populate this data run this command::

  php public/index.php data-fixture:import zf-doctrine-audit

The audit database has now been created.


Run Triggers
------------

The next step is to run the generated triggers on your target database.  This is not done directly on the database through
code but instead the trigger code is output by the tool.  We will be piping this directly to the target database::

  php public/index.php audit:trigger-tool:create

Then pipe this output to the target database such as::

  php public/index.php audit:trigger-tool:create | mysql -u user -p123 -h mysql target_database

Any triggers with the same names will be removed.  This allows you to re-run the trigger sql.


Auditing is Working
-------------------

At this point if you connect to your target database and add a new record to a table which is audited through its entity
the audit log will show up inside the audit database.

If your target database already has data in it you probably want to explore :doc:`epoch`.
