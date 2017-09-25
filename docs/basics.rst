Getting Started
===============

Installation
------------

Installation of this module uses composer. For composer documentation, please refer to
`getcomposer.org <http://getcomposer.org>`_.

``$ composer require api-skeletons/zf-doctrine-audit``

Once installed, add ``ZF\Doctrine\Audit`` to your list of modules inside
``config/application.config.php`` or ``config/modules.config.php``.


zf-component-installer
^^^^^^^^^^^^^^^^^^^^^^

If you use `zf-component-installer <https://github.com/zendframework/zf-component-installer>`_,
that plugin will install zf-doctrine-audit as a module for you.


Configuration
-------------

Copy ``config/zf-doctrine-audit.global.php.dist`` to your ``config/autoload`` directory and
rename it to ``zf-doctrine-audit.global.php``.

There are several configuration variables to customize.


target_object_manager
^^^^^^^^^^^^^^^^^^^^^

This is the service manager alias for the object manager to audit entities.  The default ``doctrine.entitymanager.orm_default`` is the same as ``doctrine/doctrine-orm-module`` default.


audit_object_manager
^^^^^^^^^^^^^^^^^^^^

This is the service manager alias for the object manager for the audit database.  You will need to add this object manager to your ORM project.  See `multiple object managers`_ for help.


audit_table_name_prefix and audit_table_name_suffix
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

These configuration variables allow you to add a prefix or suffix to the generated tables in the audit database.


epoch_import_limit
^^^^^^^^^^^^^^^^^^

When running the epoch tool data will be processed in batches to conserve memory.  This is the number to process at a time.


entities
^^^^^^^^

This associative array of entity names inside target_object_manager will be audited and an audit table will be created for each.  This array takes the format
``'Db\Entity\User' => [],``
The empty array for the entity name is reserved for future development.  It may be used to store route information for canonical paths.


joinEntities
^^^^^^^^^^^^
The joinEntities array is a list of pseudo entity names representing a many to many join across entities.

The format is the namespace of the owner entity followed by the table name (in most cases) to represent the entity.
The ownerEntity is required as is the tableName.  This information is used to find the join mapping in the metadata.

Example::

    'joinEntities' => [
        'Db\Entity\ArtistToArtistGroup' => [
            'ownerEntity' => 'Db\Entity\ArtistGroup',
            'tableName' => 'ArtistToArtistGroup',
        ],
    ],

.. _multiple object managers: http://blog.tomhanderson.com/2016/03/zf2-doctrine-configure-second-object.html
