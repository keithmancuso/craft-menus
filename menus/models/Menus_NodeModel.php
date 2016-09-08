<?php
namespace Craft;

/**
* Menus - Node model
*/
class Menus_NodeModel extends BaseElementModel
{
  protected $elementType = 'Menus_Node';

  /**
  * @access protected
  * @return array
  */
  protected function defineAttributes()
  {
    return array_merge(parent::defineAttributes(), array(
      'menuId' => AttributeType::Number,
      'linkedEntryId'  => AttributeType::Number,
      'customUrl'  => AttributeType::String,
      'linkedEntryUrl'  => AttributeType::String,

      // Just used for saving categories
      'newParentId'      => AttributeType::Number
    ));
  }

  /**
  * Returns whether the current user can edit the element.
  *
  * @return bool
  */
  public function isEditable()
  {
    return true;
  }

  /**
  * Returns the element's CP edit URL.
  *
  * @return string|false
  */
  public function getCpEditUrl()
  {
    $menu = $this->getMenu();

    if ($menu)
    {
      return UrlHelper::getCpUrl('menus/'.$menu->handle.'/'.$this->id);
    }
  }


  /**
  * Returns the nodes's menus.
  *
  * @return Menus_MenuModel|null
  */
  public function getMenu()
  {

    if ($this->menuId)
    {
      return craft()->menus->getMenuById($this->menuId);
    }
  }

  /**
  * Returns the nodes url
  *
  * @return String|null
  */
  public function getUrl()
  {

    //return '/test';
    if ($this->linkedEntryId)
    {
      // $entry = craft()->entries->getEntryById($this->linkedEntryId);
      //
      // if ($entry != null) {
      //   return $entry->url;
      // } else {
      //   return null;
      // }

      return '/'.$this->linkedEntryUrl;
    }
    else
    {
      return $this->customUrl;

    }
  }

  /**
  * Returns the nodes url
  *
  * @return String|null
  */
  public function getLink()
  {
    //return '/test';
    if ($this->linkedEntryId)
    {
      // $entry = craft()->entries->getEntryById($this->linkedEntryId);
      //
      // if ($entry != null) {
      //   return '/'.$entry->uri;
      // } else {
      //   return null;
      // }
      return '/'.$this->linkedEntryUrl;
    }
    else
    {
      return $this->customUrl;

    }
  }

  /**
  * Returns the entry of a node
  * {{ node.entry.fieldHandle }}
  *
  * @return String|null
  */
  public function getEntry()
  {
    if ($this->linkedEntryId)
    {
      $entry = craft()->entries->getEntryById($this->linkedEntryId);
      if ($entry != null) {
        return $entry;
      } else {
        return null;
      }
    }
    else
    {
      return null;
    }
  }

  /**
  * Returns the nodes active state
  *
  * @return String|null
  */
  public function getActive()
  {


    $currentUrl = craft()->request->getUrl();
    $linkUrl = $this->getUrl();

    if ($linkUrl != null )
    {
      if (strpos($currentUrl,$this->getLink()) !== false)
      {

        return true;

      }

    }

    return false;


  }

  /*
  * Returns the nodes active state
  *
  * @return String|null
  */
  public function getCurrentUrl()
  {

    $currentUrl = craft()->request->getUrl();

    return $currentUrl;

  }




}
