ORM Audit for Doctrine
==============

[![Build Status](https://travis-ci.org/API-Skeletons/zf-doctrine-audit.png)](https://travis-ci.org/API-Skeletons/zf-doctrine-audit)

Auditing for Doctrine 2.  This module creates an entity to audit a specified target entity and tracks revisions to that target.  Includes tagging of authenticated users and per-revision comments.


About
=====

This module takes a configuration of entities to audit and creates entities to audit
them and revision tracking entities.  Included is a view layer to browse the audit
records.  Routing back to live application data is supported and view helpers allow
you to find and browse to the latest audit record from a given audited entity.

Revisions pool all audited entities into revision buckets.  Each bucket contains 
the revision entity for each audited record in a flush.

Auditing is done in it's own transaction after a flush has been performed.  
Auditing takes two flushes in one transaction to complete.


Install
=======

```php
composer require "api-skeletons/zf-doctrine-audit": "^0.1"
```


Enable `config/application.config.php`:
```php
return array(
    'modules' => array(
        'ZF\\Doctrine\\Audit'
        ...
    ),
```

Copy `config/zf-doctrine-audit.global.php.dist` to `config/autoload/zf-doctrine-audit.global.php` and edit settings.

You may use different entity managers for the target and audit database.  When used this way
you must create the schema for the audit database independently.  This command line tool will
dump the SQL which you may pipe to the audit database.
```sh
vendor/bin/doctrine-module orm:schema-tool:update
```


Terminology
-----------

AuditEntity - A generated entity which maps to the Target auditable entity.  
This stores the values for the Target entity at the time a Revision is created.

Revision - An entity which stores the timestamp, comment, an user for a single entity manager 
flush which contains auditable entities.

RevisionEntity - A mapping entity which maps an AuditEntity to a Revision and maps to a Target audited entity.  
This also stores the revision type when the Target was audited.  INS, UPD, and DEL map to 
insert, update, and delete.  The primary keys of the Target are stored as an array and 
can be used to rehydrate a Target.

Target entity - An auditable entity specified as string in the audit configuration.


RevisionEntity Titles
---------------

If an entity has a __toString method it will be used to title an audit entity limited to 256 characters and stored in the RevisionEntity.  This can only be done when the entity is audited.


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

Identifier column values from the audited entity will be added to defaults to generate urls through routing.

```
    <?php
        $options = $this->auditEntityOptions($revisionEntity->getTargetEntityClass());
        $routeOptions = array_merge($options['defaults'], $revisionEntity->getEntityKeys());
    ?>
    <a class="btn btn-info" href="<?=
        $this->url($options['route'], $routeOptions);
    ?>">Data</a>
```

This is how to map from your application to it's current revision entity:

```
    <a class="btn btn-info" href="<?=
        $this->url('audit/revision-entity',
            array(
                'revisionEntityId' => $this->auditCurrentRevisionEntity($auditedEntity)->getId()
            )
        );
    ?>">
        <i class="icon-list"></i>
    </a>
```


View Helpers
------------

Return the audit service.  This is a helper class.  The class is also available via dependency injection factory ```auditService```
This class provides the following:

1. setComment();
    Set the comment for the next audit transaction.  When a comment is set it will be read at the time the audit Revision is created and added as the comment.

2. getAuditEntityValues($auditEntity);
    Returns all the fields and their values for the given audit entity.  Does not include many to many relations.

3. getEntityIdentifierValues($entity);
    Return all the identifying keys and values for an entity.

4. getRevisionEntities($entity)
    Returns all RevisionEntity entities for the given audited entity or RevisionEntity.

````
$view->auditService();
```

Return the latest revision entity for the given entity.
```
$view->auditCurrentRevisionEntity($entity);
```

Return a paginator for all revisions of the specified class name.
```
$view->auditEntityPaginator($page, $entityClassName);
```

Return a paginator for all RevisionEntity entities for the given entity or
a paginator attached to every RevisionEntity for the given audited entity class.Pass an entity or a class name string.
```
$view->auditRevisionEntityPaginator($page, $entity);
```

Return a paginator for all Revision entities.
```
$view->auditRevisionPaginator($page);
```

Returns the routing information for an entity by class name
```
$view->auditEntityOptions($entityClassName);
```


Inspired by SimpleThings
