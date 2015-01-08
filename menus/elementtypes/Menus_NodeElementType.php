<?php
namespace Craft;

/**
* Menus - Node element type
*/
class Menus_NodeElementType extends BaseElementType
{
  /**
  * Returns the element type name.
  *
  * @return string
  */
  public function getName()
  {
    return Craft::t('Menus');
  }

  /**
  * Returns whether this element type has content.
  *
  * @return bool
  */
  public function hasContent()
  {
    return true;
  }

  /**
  * Returns whether this element type has titles.
  *
  * @return bool
  */
  public function hasTitles()
  {
    return true;
  }


  /**
  * @inheritDoc IElementType::hasStatuses()
  *
  * @return bool
  */
  public function hasStatuses()
  {
    return true;
  }

  /**
  * Returns this element type's sources.
  *
  * @param string|null $context
  * @return array|false
  */
  public function getSources($context = null)
  {
    $sources = array();

      foreach (craft()->menus->getAllMenus() as $menu)
      {
        $key = 'menu:'.$menu->id;

        $sources[$key] = array(
        'label'    => $menu->name,
        'criteria' => array('menuId' => $menu->id),
        'structureId'  => $menu->structureId,
        'structureEditable' => true
        );
      }

      return $sources;
    }

    /**
    * @inheritDoc IElementType::defineSortableAttributes()
    *
    * @retrun array
    */
    public function defineSortableAttributes()
    {
      $attributes = array(
        'title' => Craft::t('Title')
      );


      return $attributes;
    }

    /**
    * Returns the attributes that can be shown/sorted by in table views.
    *
    * @param string|null $source
    * @return array
    */
    public function defineTableAttributes($source = null)
    {
      return array(
      'title'     => Craft::t('Title'),
      'link'     => Craft::t('Link'),
      //'url'     => Craft::t('Url'),
      );
    }

    /**
    * Returns the table view HTML for a given attribute.
    *
    * @param BaseElementModel $element
    * @param string $attribute
    * @return string
    */
    public function getTableAttributeHtml(BaseElementModel $element, $attribute)
    {
      switch ($attribute)
      {

        default:
        {
          return parent::getTableAttributeHtml($element, $attribute);
        }
      }
    }

    /**
    * Defines any custom element criteria attributes for this element type.
    *
    * @return array
    */
    public function defineCriteriaAttributes()
    {
      return array(
      'menu'   => AttributeType::Mixed,
      'menuId' => AttributeType::Mixed,
      'order'      => array(AttributeType::String, 'default' => 'lft'),
      );
    }

    /**
    * Modifies an element query targeting elements of this type.
    *
    * @param DbCommand $query
    * @param ElementCriteriaModel $criteria
    * @return mixed
    */
    public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
    {
      $query
      ->addSelect('nodes.menuId, nodes.linkedEntryId, nodes.customUrl, i18n.uri linkedEntryUrl')
      ->join('menus_nodes nodes', 'nodes.id = elements.id')
      ->join('menus menus', 'menus.id = nodes.menuId')
      ->leftJoin('elements_i18n i18n', 'i18n.elementId = nodes.linkedEntryId')

      ->leftJoin('structures structures', 'structures.id = menus.structureId')
      ->leftJoin('structureelements structureelements', array('and', 'structureelements.structureId = structures.id', 'structureelements.elementId = nodes.id'));


      if ($criteria->menuId)
      {
        $query->andWhere(DbHelper::parseParam('nodes.menuId', $criteria->menuId, $query->params));
      }

      if ($criteria->menu)
      {
        $query->andWhere(DbHelper::parseParam('menus.handle', $criteria->menu, $query->params));
      }

      // if ($criteria->startDate)
      // {
      //   $query->andWhere(DbHelper::parseDateParam('entries.startDate', $criteria->startDate, $query->params));
      // }
      //
      // if ($criteria->endDate)
      // {
      //   $query->andWhere(DbHelper::parseDateParam('entries.endDate', $criteria->endDate, $query->params));
      // }
    }

    /**
    * Populates an element model based on a query result.
    *
    * @param array $row
    * @return array
    */
    public function populateElementModel($row)
    {
      return Menus_NodeModel::populateModel($row);
    }

    /**
    * Returns the HTML for an editor HUD for the given element.
    *
    * @param BaseElementModel $element
    * @return string
    */
    public function getEditorHtml(BaseElementModel $element)
    {

      $linkedElements[] = craft()->entries->getEntryById($element->linkedEntryId);
      $selectElement = craft()->elements->getElementType('Entry');


      // Start/End Dates
      $html = craft()->templates->render('menus/_editor', array(
      'element' => $element,
      'linkedElements' => $linkedElements,
      'selectElement' => $selectElement
      ));

      // Everything else
      $html .= parent::getEditorHtml($element);

      return $html;
    }

    /**
    * @inheritDoc IElementType::saveElement()
    *
    * @param BaseElementModel $element
    * @param array            $params
    *
    * @return bool
    */
    public function saveElement(BaseElementModel $element, $params)
    {
      //var_dump($element);
      //exit;

      if (isset($params['customUrl']))
      {
        $element->customUrl = $params['customUrl'];
      }

      $linkedEntry  = $params['linkedEntryId'];

      if (count($linkedEntry) > 0 ) {
        $element->linkedEntryId = $linkedEntry[0];
      }

      return craft()->menus_nodes->saveNode($element);
    }

    /**
    * @inheritDoc IElementType::getAvailableActions()
    *
    * @param string|null $source
    *
    * @return array|null
    */
    public function getAvailableActions($source = null)
    {
      if (preg_match('/^menu:(\d+)$/', $source, $matches))
      {
        $menu = craft()->menus->getMenuById($matches[1]);
      }

      if (empty($menu))
      {
        return;
      }

      $actions = array();

      // Set Status
      $actions[] = 'SetStatus';


      // Edit
      $editAction = craft()->elements->getAction('Edit');
      $editAction->setParams(array(
        'label' => Craft::t('Edit node'),
      ));
      $actions[] = $editAction;

      // New Child
      $structure = craft()->structures->getStructureById($menu->structureId);

      if ($structure)
      {
        $newChildAction = craft()->elements->getAction('NewChild');
        $newChildAction->setParams(array(
          'label'       => Craft::t('Create a new child node'),
          'maxLevels'   => $structure->maxLevels,
          'newChildUrl' => 'menus/'.$menu->handle.'/new',
          ));
          $actions[] = $newChildAction;
        }

        // Delete
        $deleteAction = craft()->elements->getAction('Delete');
        $deleteAction->setParams(array(
        'confirmationMessage' => Craft::t('Are you sure you want to delete the selected nodes?'),
        'successMessage'      => Craft::t('Nodes deleted.'),
        ));
        $actions[] = $deleteAction;

        return $actions;
      }


  }
