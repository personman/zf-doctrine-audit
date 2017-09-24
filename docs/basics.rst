Basics
======

Installation
------------

Installation of this module uses composer. For composer documentation, please refer to
`getcomposer.org <http://getcomposer.org>`_.

``$ composer require api-skeletons/zf-doctrine-audit``

Once installed, add ``ZF\Doctrine\Audit`` to your list of modules inside
``config/application.config.php`` or ``config/modules.config.php``.


zf-component-installer
----------------------

If you use `zf-component-installer <https://github.com/zendframework/zf-component-installer>`_,
that plugin will install zf-doctrine-audit as a module for you.


Configuration
-------------

Copy ``config/zf-doctrine-audit.global.php.dist`` to your ``config/autoload`` directory and
rename it to ``zf-doctrine-audit.global.php``.

At a minimum add your own list of entities and joinEntities to audit and remove the placeholders.
