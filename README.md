ORM Audit for Doctrine
======================

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-audit.png)](https://travis-ci.org/API-Skeletons/zf-doctrine-audit)

This module implements auditing against a target object manager and list of entities.  
Using an object manager configured independently of the target object manager the audit tables may
share the same database or use a different database for auditing.  

![Entity Relationship Diagram](https://raw.githubusercontent.com/API-Skeletons/zf-doctrine-audit/master/media/zf-doctrine-audit-erd.png)

Entity Relationship Diagram created with [Skipper](https://skipper18.com)


About
=====

This module takes a configuration of entities to audit and creates entities to audit
them and revision tracking entities.  Included is a view layer to browse the audit
records.  Routing back to live application data is supported and view helpers allow
you to find and browse to the latest audit record from a given audited entity.

Auditing is grouped by flush() events into a Revision.  You may create a comment for each revision.  
The single flush() for auditing is done independent and after the flush() from the target object manager.

The original concept was inspired by [SimpleThings](https://packagist.org/packages/simplethings/entity-audit-bundle)
in Jan 2013



Install
=======

```php
composer require "api-skeletons/zf-doctrine-audit": "^1.0"
```

Add to `config/application.config.php`:
```php
return array(
    'modules' => array(
        'ZF\\Doctrine\\Audit'
        ...
    ),
```

Copy `config/zf-doctrine-audit.global.php.dist` to `config/autoload/zf-doctrine-audit.global.php` and edit settings.
A separate entity manager is required for auditing.  A separate database is optional.

You may use different entity managers for the target and audit database.  
This command line tool will dump the SQL which you may pipe to the audit database.
```sh
index.php zf-doctrine-audit:schema-tool:update
```

Fixtures for zf-doctrine-audit are requried.  They are based on the configured entity audit list.
```sh
index.php zf-doctrine-audit:data-fixture:import
```


Revision Comment
----------------

To add a comment to a revision get `ZF\Doctrine\Audit\Service\RevisionComment` from the service manager
and call `->setMessage(string)` before flushing.


RevisionEntity Titles
---------------

If an entity has a __toString method it will be used to title an audit entity and stored in the Revision.  


Authentication
--------------

You may specify an entity to serve as the user entity for mapping revisions to users.  
You may configure a custom authentication service too.  By default these map to 
Zend\Authentication\AuthenticationService.

The user entity must implement ZF\Doctrine\Audit\Entity\UserInterface  
The authentication service must implement hasIdentity and getIdentity which returns an instance of the current user entity.


Routing
-------

To ease browsing of the audit record you may include routing information in the entities() configuration.

```
    'Db\Entity\Song' => array(
        'route' => 'default',
        'defaults' => array(
            'controller' => 'song',
            'action' => 'detail',
        ),
    ),
```

