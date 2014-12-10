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

    if ($this->linkedEntryId)
    {
      $entry = craft()->entries->getEntryById($this->linkedEntryId);

      if ($entry != null) {
        return $entry->url;
      } else {
        return null;
      }
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

    if ($this->linkedEntryId)
    {
      $entry = craft()->entries->getEntryById($this->linkedEntryId);

      if ($entry != null) {
        return '/'.$entry->uri;
      } else {
        return null;
      }

    }
    else
    {
      return $this->customUrl;

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

    $children = $this->getChildren();

    if (count($children) > 0  &&  $this->getUrl() == null ) {

      foreach ($children as &$child) {
        if ($child->getLink() == $currentUrl) {
          return true;
        }
      }
      return false;

    } else {
      if ($currentUrl == $this->getLink() )
      {
        return true;
      }
      else
      {
        return false;
      }
    }





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
