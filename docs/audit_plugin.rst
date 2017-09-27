Audit Plugin
============

This module uses `API-Skeletons/zf-doctrine-repository <https://github.com/API-Skeletons/zf-doctrine-repository>`_ to provide a plugin
in Doctrine repositories to run common audit queries.

This plugin will be extended in the future.  Currently these functions are supported


getRevisionEntityCollection($entity)
------------------------------------

This will return the complete audit history for the passed entity.  


getOldestRevisionEntity($entity)
--------------------------------

One of the most common fields in databases is createdAt (or date_created, etc).  With auditing this field is unnecessary in the target
database and can be derived by inspecting the oldest audit record for an entity. 

By fetching the oldest revision entity you can get the createdAt with::
  
  $oldestRevisionEntity = $this->plugin('audit')->getOldestRevisionEntity($entity);
  $createdAt = $oldestRevisionEntity->getRevisionEntity()->getRevision()->getCreatedAt();
  

getNewestRevisionEntity($entity)
--------------------------------

To find the latest information about an entity, such as when it was last edited, fetch the newest revision entity.
