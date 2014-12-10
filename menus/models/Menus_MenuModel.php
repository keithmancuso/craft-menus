<?php
namespace Craft;

/**
 * Menus - Menu model
 */
class Menus_MenuModel extends BaseModel
{
  /**
   * Use the translated calendar name as the string representation.
   *
   * @return string
   */
  function __toString()
  {
    return Craft::t($this->name);
  }

  /**
   * @access protected
   * @return array
   */
  protected function defineAttributes()
  {
    return array(
      'id'            => AttributeType::Number,
      'name'          => AttributeType::String,
      'handle'        => AttributeType::String,
      'type'          => AttributeType::String,
      'maxLevels'     => AttributeType::Number,
      'structureId'   => AttributeType::Number
    );
  }

}
