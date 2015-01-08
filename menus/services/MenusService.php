<?php
namespace Craft;

/**
* Menus service
*/
class MenusService extends BaseApplicationComponent
{
  private $_allMenuIds;
  private $_menusById;
  private $_fetchedAllMenus = false;

  /**
  * Returns all of the menu IDs.
  *
  * @return array
  */
  public function getAllMenuIds()
  {
    if (!isset($this->_allMenuIds))
    {
      if ($this->_fetchedAllMenus)
      {
        $this->_allMenuIds = array_keys($this->_menusById);
      }
      else
      {
        $this->_allMenuIds = craft()->db->createCommand()
        ->select('id')
        ->from('menus')
        ->queryColumn();
      }
    }

    return $this->_allMenuIds;
  }

  /**
  * Returns all calendars.
  *
  * @param string|null $indexBy
  * @return array
  */
  public function getAllMenus($indexBy = null)
  {
    if (!$this->_fetchedAllMenus)
    {
      $menuRecords = Menus_MenuRecord::model()->ordered()->findAll();
      $this->_menusById = Menus_MenuModel::populateModels($menuRecords, 'id');
      $this->_fetchedAllMenus = true;
    }

    if ($indexBy == 'id')
    {
      return $this->_menusById;
    }
    else if (!$indexBy)
    {
      return array_values($this->_menusById);
    }
    else
    {
      $calendars = array();

      foreach ($this->_menusById as $menu)
      {
        $calendars[$menu->$indexBy] = $menu;
      }

      return $menus;
    }
  }

  /**
  * Gets the total number of calendars.
  *
  * @return int
  */
  public function getTotalMenus()
  {
    return count($this->getAllMenuIds());
  }

  /**
  * Returns a menu by its ID.
  *
  * @param $menuId
  * @return Menus_MenuModel|null
  */
  public function getMenuById($menuId)
  {
    if (!isset($this->_menusById) || !array_key_exists($menuId, $this->_menusById))
    {
      $menuRecord = Menus_MenuRecord::model()->findById($menuId);

      if ($menuRecord)
      {
        $this->_menusById[$menuId] = Menus_MenuModel::populateModel($menuRecord);
      }
      else
      {
        $this->_menusById[$menuId] = null;
      }
    }

    return $this->_menusById[$menuId];
  }

  /**
  * Gets a menu by its handle.
  *
  * @param string $calendarHandle
  * @return Menus_MenuModel|null
  */
  public function getMenuByHandle($calendarHandle)
  {
    $menuRecord = Menus_MenuRecord::model()->findByAttributes(array(
    'handle' => $calendarHandle
    ));

    if ($menuRecord)
    {
      return Menus_MenuModel::populateModel($menuRecord);
    }
  }

  /**
  * Saves a menu.
  *
  * @param Menus_MenuModel $menu
  * @throws \Exception
  * @return bool
  */
  public function saveMenu(Menus_MenuModel $menu)
  {



    if ($menu->id)
    {
      $menuRecord = Menus_MenuRecord::model()->findById($menu->id);

      if (!$menuRecord)
      {
        throw new Exception(Craft::t('No menu exists with the ID “{id}”', array('id' => $menu->id)));
      }

      $oldMenu = Menus_MenuModel::populateModel($menuRecord);
      $isNewMenu = false;
    }
    else
    {
      $menuRecord = new Menus_MenuRecord();
      $isNewMenu = true;
    }

    $menuRecord->name       = $menu->name;
    $menuRecord->handle     = $menu->handle;
    $menuRecord->type     = $menu->type;

    $menuRecord->maxLevels     = $menu->maxLevels;


    $menuRecord->validate();
    $menu->addErrors($menuRecord->getErrors());

    if (!$menu->hasErrors())
    {
      $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
      try
      {

        if ($isNewMenu)
        {
          $structure = new StructureModel();
          $structure->maxLevels = $menu->maxLevels;
          craft()->structures->saveStructure($structure);

          // update the menu record/model with the new structure ID
          $menuRecord->structureId = $structure->id;
          $menu->structureId = $structure->id;
        }
        else
        {

          $structure =  craft()->structures->getStructureById($menu->structureId);
          $structure->maxLevels = $menu->maxLevels;


          craft()->structures->saveStructure($structure);

        }


        // Save it!
        $menuRecord->save(false);

        // Now that we have a menu ID, save it on the model
        if (!$menu->id)
        {
          $menu->id = $menuRecord->id;
        }

        // Might as well update our cache of the menu while we have it.
        $this->_menusById[$menu->id] = $menu;

        if ($transaction !== null)
        {
          $transaction->commit();
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

      return true;
    }
    else
    {
      return false;
    }
  }

  /**
  * Deletes a menu by its ID.
  *
  * @param int $menuId
  * @throws \Exception
  * @return bool
  */
  public function deleteMenuById($menuId)
  {
    if (!$menuId)
    {
      return false;
    }

    $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
    try
    {


      // Grab the event ids so we can clean the elements table.
      $nodeIds = craft()->db->createCommand()
      ->select('id')
      ->from('menus_nodes')
      ->where(array('menuId' => $menuId))
      ->queryColumn();

      craft()->elements->deleteElementById($nodeIds);

      $affectedRows = craft()->db->createCommand()->delete('menus', array('id' => $menuId));

      if ($transaction !== null)
      {
        $transaction->commit();
      }

      return (bool) $affectedRows;
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


}
