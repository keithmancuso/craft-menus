<?php
namespace Craft;

class MenusVariable
{
  function getNodes($menuHandle)
  {
    $criteria = craft()->elements->getCriteria('Menus_Node');
    $criteria->menu = $menuHandle;
    return $criteria;
  }

  function getFullMenu($menuHandle, $type)
  {

    $criteria = craft()->elements->getCriteria('Menus_Node');
    $criteria->menu = $menuHandle;
    return null;

  }
}
