Epoch
=====

When this repository is first applied to an existing project there will probably already be data in the database which
you want to start auditing.  Creating a epoch record for each row will give auditing a reasonable starting point.

Like the trigger tool, creating an epoch should be piped to the database, but for epoch **you must pipe to the audit database**::
 
  php public/index.php audit:epoch:import

Piping this output looks like

  php public/index.php audit:epoch:import | mysql -u user -p123 -h mysql audit

The epoch tool uses the configuration variable ``epoch_import_limit``.  This variable will paginate the epoch audit record creation.
The default of 200 is acceptable.  

TODO:
The epoch tool will only create an epoch record for rows which do not already have an epoch record.
