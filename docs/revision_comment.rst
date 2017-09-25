Revision Comment
================

In order to save which user made which change inside the ORM the ``ZF\Doctrine\Audit\RevisionComment`` class exists.  
This class is managed by the service manager so only one copy exists.  Before you call a ``flush();`` to make changes to 
ORM data you may populate the ``RevisionComment`` to save additional information.


Fetching RevisionComment
----------------------------

When you wish to use the ``RevisionComment`` inject it via a factory::
   
  use ZF\Doctrine\Audit\RevisionComment;
   
  $instance->setRevisionComment($container->get(RevisionComment::class);  
  
There is a trait and interface you may include located in the ``persistence`` directory for setting and getting the ``RevisionComment``.


Using RevisionComment
---------------------

Before you ``flush();`` your object manager set the values on the ``RevisionComment``.  The RevisionComment will be cleared after ``flush();``
The value you may set is ``comment``.  


Custom Identity for Revision Auditing
-------------------------------------

The ``Revision`` entity has space for ``userId``, ``userName``, and ``userEmail``.  These are populated based on the authenticated user.
See ``src/EventListener/PostFlush.php``.  ``ZF\OAuth2\Doctrine\Identity\AuthenticatedIdentity`` is handled natively as is 
``ZF\MvcAuth\Identity\AuthenticatedIdentity`` but if you're not using these identity strategies you'll have to write your own PostFlush 
handler to update the ``Revision`` and override it in the service manager using the key 
``ZF\Doctrine\Audit\EventListener\PostFlush``.
