<?php
namespace Craft;

/**
* Menus controller
*/
class MenusController extends BaseController
{
  /**
  * Menu index
  */
  public function actionMenuIndex()
  {
    $variables['menus'] = craft()->menus->getAllMenus();

    $this->renderTemplate('menus/menus', $variables);
  }

  /**
  * Edit a menu.
  *
  * @param array $variables
  * @throws HttpException
  * @throws Exception
  */
  public function actionEditMenu(array $variables = array())
  {
    $variables['brandNewMenu'] = false;

    if (!empty($variables['menuId']))
    {
      if (empty($variables['menu']))
      {
        $variables['menu'] = craft()->menus->getMenuById($variables['menuId']);

        if (!$variables['menu'])
        {
          throw new HttpException(404);
        }
      }

      $variables['title'] = $variables['menu']->name;
    }
    else
    {
      if (empty($variables['menu']))
      {
        $variables['menu'] = new Menus_MenuModel();
        $variables['brandNewMenu'] = true;
      }

      $variables['title'] = Craft::t('Create a new menu');
    }

    $variables['crumbs'] = array(
    array('label' => Craft::t('Settings'), 'url' => UrlHelper::getUrl('settings')),
    array('label' => Craft::t('Plugins'), 'url' => UrlHelper::getUrl('settings/plugins')),

    array('label' => Craft::t('Menus'), 'url' => UrlHelper::getUrl('settings/plugins/menus')),
    );

    $this->renderTemplate('menus/menus/_edit', $variables);
  }

  /**
  * Saves a menu
  */
  public function actionSaveMenu()
  {
    $this->requirePostRequest();

    $menu = new Menus_MenuModel();

    // Shared attributes
    $menu->id         = craft()->request->getPost('menuId');
    $menu->name       = craft()->request->getPost('name');
    $menu->handle     = craft()->request->getPost('handle');
    $menu->type       = craft()->request->getPost('type');
    $menu->maxLevels     = craft()->request->getPost('maxLevels');
    $menu->structureId     = craft()->request->getPost('structureId');


    // Save it
    if (craft()->menus->saveMenu($menu))
    {
      craft()->userSession->setNotice(Craft::t('Menu saved.'));
      $this->redirectToPostedUrl($menu);
    }
    else
    {
      craft()->userSession->setError(Craft::t('Couldn’t save menu.'));
    }

    // Send the menu back to the template
    craft()->urlManager->setRouteVariables(array(
    'menu' => $menu
    ));
  }

  /**
  * Deletes a menu.
  */
  public function actionDeleteMenu()
  {
    $this->requirePostRequest();
    $this->requireAjaxRequest();

    $menuId = craft()->request->getRequiredPost('id');

    craft()->menus->deleteMenuById($menuId);
    $this->returnJson(array('success' => true));
  }
}
