<?php
namespace Craft;

class m160725_152400_menus_addNweWindowColumn extends BaseMigration
{
    public function safeUp()
    {
        return parent::addColumn('menus_nodes', 'newWindow', 'TINYINT');
    }

    public function safeDown()
    {
        return parent::dropColumn('menus_nodes', 'newWindow');
    }
}
