<?php

namespace Craft;

class MenusPlugin extends BasePlugin
{

  function getName()
  {
    return Craft::t('Menus');
  }

  function getVersion()
  {
    return '0.9';
  }

  function getDeveloper()
  {
    return 'Familiar';
  }

  function getDeveloperUrl()
  {
    return 'http://familiar.nyc';
  }

  protected function defineSettings()
  {
    return array(

    );
  }

  public function getSettingsHtml()
  {

    $menus = craft()->menus->getAllMenus();


    return craft()->templates->render('menus/settings', array(
      'settings' => $this->getSettings(),
      'menus' => $menus
    ));
  }

  public function hasCpSection()
  {

    $menus = craft()->menus->getAllMenus();

    if (count($menus) == 0) {
      return false;
    } else {
      return true;
    }

  }


  public function registerCpRoutes()
  {


    return array(
      'menus/settings'                                     => array('action' => 'menus/menuIndex'),
      'menus/menu/new'                                 => array('action' => 'menus/editMenu'),
      'menus/menu/(?P<menuId>\d+)'                 => array('action' => 'menus/editMenu'),
      'menus'                                               => array('action' => 'menus/nodes/nodeIndex'),
      'menus/(?P<menuHandle>{handle})/new'              => array('action' => 'menus/nodes/editNode'),
      'menus/(?P<menuHandle>{handle})/(?P<nodeId>\d+)' => array('action' => 'menus/nodes/editNode'),
    );
  }

  public function init()
  {
    craft()->structures->onMoveElement = function(Event $event) {
      $element = $event->params['element'];
      craft()->templateCache->deleteCachesByElementId($element['id']);
    };
  }

}
