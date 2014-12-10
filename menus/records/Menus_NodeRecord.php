<?php
namespace Craft;

/**
* Events - Event record
*/
class Menus_NodeRecord extends BaseRecord
{
  /**
  * @return string
  */
  public function getTableName()
  {
    return 'menus_nodes';
  }

  /**
  * @access protected
  * @return array
  */
  protected function defineAttributes()
  {
    return array(
      'linkedEntryId' => array(AttributeType::Number, 'required' => false),
      'customUrl'   => array(AttributeType::String, 'required' => false)
    );
  }

  /**
  * @return array
  */
  public function defineRelations()
  {
    return array(
      'element'  => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
      'menu' => array(static::BELONGS_TO, 'Menus_MenuRecord', 'required' => true, 'onDelete' => static::CASCADE),
      'linkedEntry' => array(static::HAS_ONE, 'EntryRecord', 'linkedEntryId','required' => false),

    );
  }
}
