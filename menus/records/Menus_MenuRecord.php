<?php
namespace Craft;

/**
* Menus - Menu record
*/
class Menus_MenuRecord extends BaseRecord
{
  /**
  * @return string
  */
  public function getTableName()
  {
    return 'menus';
  }

  /**
  * @access protected
  * @return array
  */
  protected function defineAttributes()
  {
    return array(
      'name'          => array(AttributeType::Name, 'required' => true),
      'handle'        => array(AttributeType::Handle, 'required' => true),
      'maxLevels'     => AttributeType::Number,
      'type'          => AttributeType::String,
      'structureId'   => AttributeType::Number
    );
  }

  /**
  * @return array
  */
  public function defineRelations()
  {
    return array(
      'nodes'      => array(static::HAS_MANY, 'Menus_NodeRecord', 'nodeId'),
    );
  }

  /**
  * @return array
  */
  public function defineIndexes()
  {
    return array(
      array('columns' => array('name'), 'unique' => true),
      array('columns' => array('handle'), 'unique' => true),
    );
  }

  /**
  * @return array
  */
  public function scopes()
  {
    return array(
    'ordered' => array('order' => 'name'),
    );
  }
}
