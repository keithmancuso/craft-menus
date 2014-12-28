<?php
namespace Craft;

/**
* Menus_Nodes service
*/
class Menus_NodesService extends BaseApplicationComponent
{
  /**
  * Returns an node by its ID.
  *
  * @param int $nodeId
  * @return Menus_NodeModel|null
  */
  public function getNodeById($nodeId)
  {
    return craft()->elements->getElementById($nodeId, 'Menus_Node');
  }

  /**
  * Saves an node.
  *
  * @param Menus_NodeModel $node
  * @throws Exception
  * @return bool
  */
  public function saveNode(Menus_NodeModel $node)
  {
    $isNewNode = !$node->id;

    $hasNewParent = $this->_checkForNewParent($node);


    if ($hasNewParent)
    {
      if ($node->newParentId)
      {
        $parentNode = $this->getNodeById($node->newParentId);

        if (!$parentNode)
        {
          throw new Exception(Craft::t('No node exists with the ID “{id}”', array('id' => $category->newParentId)));
        }
      }
      else
      {
        $parentNode = null;
      }

      $node->setParent($parentNode);
    }


    // Event data
    if (!$isNewNode)
    {
      $nodeRecord = Menus_NodeRecord::model()->findById($node->id);

      if (!$nodeRecord)
      {
        throw new Exception(Craft::t('No node exists with the ID “{id}”', array('id' => $node->id)));
      }
    }
    else
    {
      $nodeRecord = new Menus_NodeRecord();
    }

    $nodeRecord->menuId = $node->menuId;
    $nodeRecord->linkedEntryId  = $node->linkedEntryId;
    $nodeRecord->customUrl    = $node->customUrl;

    $nodeRecord->validate();
    $node->addErrors($nodeRecord->getErrors());

    if (!$node->hasErrors())
    {
      $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
      try
      {
        // Fire an 'onBeforeSaveEvent' node
        $this->onSaveNode(new Event($this, array(
        'node'      => $node,
        'isNewNode' => $isNewNode
        )));


        if (craft()->elements->saveElement($node))
        {
          // Now that we have an element ID, save it on the other stuff
          if ($isNewNode)
          {
            $nodeRecord->id = $node->id;
          }

          $nodeRecord->save(false);


          //Has the parent changed?
          if ($hasNewParent)
          {
            if (!$node->newParentId)
            {
              craft()->structures->appendToRoot($node->getMenu()->structureId, $node);
            }
            else
            {
              craft()->structures->append($node->getMenu()->structureId, $node, $parentNode);
            }
          }

          //Update the category's descendants, who may be using this category's URI in their own URIs
          craft()->elements->updateDescendantSlugsAndUris($node);

          // Fire an 'onSaveEvent' node
          $this->onSaveNode(new Event($this, array(
          'node'      => $node,
          'isNewNode' => $isNewNode
          )));

          if ($transaction !== null)
          {
            $transaction->commit();
          }

          return true;
        }
      }
      catch (\Exception $e)
      {
        if ($transaction !== null)
        {
          $transaction->rollback();
        }

        throw $e;
      }
    }



    return false;
  }

  // Events

  /**
  * Fires an 'onBeforeSaveEvent' node.
  *
  * @param Event $node
  */
  public function onBeforeSaveNode(Event $node)
  {
    $this->raiseEvent('onBeforeSaveNode', $node);
  }

  /**
  * Fires an 'onSaveEvent' node.
  *
  * @param Event $node
  */
  public function onSaveNode(Event $node)
  {
    $this->raiseEvent('onSaveNode', $node);
  }

  /**
  * Checks if an category was submitted with a new parent category selected.
  *
  * @param Menus_NodeModel $node
  *
  * @return bool
  */
  private function _checkForNewParent(Menus_NodeModel $node)
  {
    // Is it a brand new category?
    if (!$node->id)
    {
      return true;
    }

    // Was a new parent ID actually submitted?
    if ($node->newParentId === null)
    {
      return false;
    }

    // Is it set to the top level now, but it hadn't been before?
    if ($node->newParentId === '' && $node->level != 1)
    {
      return true;
    }

    // Is it set to be under a parent now, but didn't have one before?
    if ($node->newParentId !== '' && $node->level == 1)
    {
      return true;
    }

    // Is the newParentId set to a different category ID than its previous parent?
    $criteria = craft()->elements->getCriteria('Menus_Node');
    $criteria->ancestorOf = $node;
    $criteria->ancestorDist = 1;
    $criteria->status = null;
    $criteria->localeEnabled = null;

    $oldParent = $criteria->first();
    $oldParentId = ($oldParent ? $oldParent->id : '');

    if ($node->newParentId != $oldParentId)
    {
      return true;
    }

    // Must be set to the same one then
    return false;
  }
}
