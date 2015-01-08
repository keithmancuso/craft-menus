<?php
namespace Craft;

/**
* Menus controller
*/
class Menus_NodesController extends BaseController
{
  /**
  * Menu index
  */
  public function actionNodeIndex()
  {
    $variables['menus'] = craft()->menus->getAllMenus();

    $this->renderTemplate('menus/_index', $variables);
  }

  /**
  * Edit an node.
  *
  * @param array $variables
  * @throws HttpException
  */
  public function actionEditNode(array $variables = array())
  {
    if (!empty($variables['menuHandle']))
    {
      $variables['menu'] = craft()->menus->getMenuByHandle($variables['menuHandle']);
    }
    else if (!empty($variables['menuId']))
    {
      $variables['menu'] = craft()->menus->getMenuById($variables['menuId']);
    }

    if (empty($variables['menu']))
    {
      throw new HttpException(404);
    }

    // Now let's set up the actual node
    if (empty($variables['node']))
    {
      if (!empty($variables['nodeId']))
      {
        $variables['node'] = craft()->menus_nodes->getNodeById($variables['nodeId']);

        if (!$variables['node'])
        {
          throw new HttpException(404);
        }
      }
      else
      {
        $variables['node'] = new Menus_NodeModel();
        $variables['node']->menuId = $variables['menu']->id;
      }
    }

    if (!$variables['node']->id)
    {
      $variables['title'] = Craft::t('Create a new node');
      $variables['linkedElements'] = array();
    }
    else
    {
      $variables['title'] = $variables['node']->title;

      $variables['linkedElements'][] = craft()->entries->getEntryById($variables['node']->linkedEntryId);
    }

    if ($variables['menu']->maxLevels != 1)
    {
      $variables['elementType'] = new ElementTypeVariable(craft()->elements->getElementType('Menus_Node'));

      // Define the parent options criteria
      $variables['parentOptionCriteria'] = array(
        'menuId'       => $variables['menu']->id,
        'status'        => null,
        'localeEnabled' => null,
      );

      if ($variables['menu']->maxLevels)
      {
        $variables['parentOptionCriteria']['level'] = '< '.$variables['menu']->maxLevels;
      }

      if ($variables['node']->id)
      {
        // Prevent the current category, or any of its descendants, from being options
        $idParam = array('and', 'not '.$variables['node']->id);

        $descendantCriteria = craft()->elements->getCriteria('Menus_Node');
        $descendantCriteria->descendantOf = $variables['node'];
        $descendantCriteria->status = null;
        $descendantCriteria->localeEnabled = null;
        $descendantIds = $descendantCriteria->ids();

        foreach ($descendantIds as $id)
        {
          $idParam[] = 'not '.$id;
        }

        $variables['parentOptionCriteria']['id'] = $idParam;
      }

      // Get the initially selected parent
      $parentId = craft()->request->getParam('parentId');

      if ($parentId === null && $variables['node']->id)
      {
        $parentIds = $variables['node']->getAncestors(1)->status(null)->localeEnabled(null)->ids();

        if ($parentIds)
        {
          $parentId = $parentIds[0];
        }
      }

      if ($parentId)
      {
        $variables['parent'] = craft()->menus_nodes->getNodeById($parentId);
      }
    }


    // Breadcrumbs
    $variables['crumbs'] = array(
    array('label' => Craft::t('Menus'), 'url' => UrlHelper::getUrl('menus')),
    array('label' => $variables['menu']->name, 'url' => UrlHelper::getUrl('menus'))
    );

    // Set the "Continue Editing" URL
    $variables['continueEditingUrl'] = 'menus/'.$variables['menu']->handle.'/{id}';

    $variables['selectElement'] = craft()->elements->getElementType('Entry');

    // Render the template!
    $this->renderTemplate('menus/_edit', $variables);
  }

  /**
  * Saves an node.
  */
  public function actionSaveNode()
  {
    $this->requirePostRequest();

    $nodeId = craft()->request->getPost('nodeId');

    if ($nodeId)
    {
      $node = craft()->menus_nodes->getNodeById($nodeId);

      if (!$node)
      {
        throw new Exception(Craft::t('No node exists with the ID “{id}”', array('id' => $nodeId)));
      }
    }
    else
    {
      $node = new Menus_NodeModel();
    }

    // Set the node attributes, defaulting to the existing values for whatever is missing from the post data
    $node->menuId = craft()->request->getPost('menuId', $node->menuId);
    $linkedEntry  = craft()->request->getPost('linkedEntryId' );
    $node->enabled       = (bool) craft()->request->getPost('enabled', $node->enabled);



    if (is_array($linkedEntry)) {
      $node->linkedEntryId = isset($linkedEntry[0]) ? $linkedEntry[0] : null;
    }

    $node->customUrl      = craft()->request->getPost('customUrl', $node->customUrl );
    $node->getContent()->title = craft()->request->getPost('title', $node->title);

    // Parent
    $parentId = craft()->request->getPost('parentId');

    if (is_array($parentId))
    {
      $parentId = isset($parentId[0]) ? $parentId[0] : null;
    }

    $node->newParentId = $parentId;

    $node->setContentFromPost('fields');

    if (craft()->menus_nodes->saveNode($node))
    {
      craft()->userSession->setNotice(Craft::t('Node saved.'));
      $this->redirectToPostedUrl($node);
    }
    else
    {
      craft()->userSession->setError(Craft::t('Couldn’t save node.'));

      // Send the node back to the template
      craft()->urlManager->setRouteVariables(array(
      'node' => $node
      ));
    }
  }

  /**
  * Deletes an node.
  */
  public function actionDeleteNode()
  {
    $this->requirePostRequest();

    $nodeId = craft()->request->getRequiredPost('nodeId');

    if (craft()->elements->deleteElementById($nodeId))
    {
      craft()->userSession->setNotice(Craft::t('Node deleted.'));
      $this->redirectToPostedUrl();
    }
    else
    {
      craft()->userSession->setError(Craft::t('Couldn’t delete node.'));
    }
  }
}
