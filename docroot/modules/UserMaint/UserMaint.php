<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("mj_RightsUserDbInterface.php");
mj_require_once("mj_RightsGroupDbInterface.php");

// This class handles the basic User editing UI

class UserMaint //implements mj_ModuleInterface
{
  static public function init() { }

  /**
   * This is a configured in function assigned to a slot
   */
  static public function getHeadScript($tplname,$slotname)
  {
    return "<script>alert('script from UserMaint.getHeadScript() tplname[$tplname] slotname[$slotname]');</script>\n";
  }

  /**
   * This is a configured in function assigned to a slot
   */
  static public function leftMenu($tplname,$slotname)
  {
    return "<br>UserMaint.leftMenu: tplname[$tplname] slotname[$slotname]'<br>\n";
  }

  /**
   * This is a configured in function assigned to a slot
   */
  static public function getHeadBlock($tplname,$slotname)
  {
    return "<br><button type=button id=timerButton value='Timer UserMaint Button'>Timer UserMaint Button</button><br>\n";
  }

  /**
   * return an array of navigation items.
   * this array should be of the form array("group", array("user display text","link target","priv string")) where:
   *   - "group"             = menu item grouping - can be an empty string in which case there will still be a grouping
   *   - "user display text" = what will be displayed to the user
   *   - "link target"       = a URL
   *   - "priv string"       = a rights string to be tested before display
   **/
  static public function navigationEntries($blockName)
  {
    return array(
      array(
        'sectionTitle' => 'User Controls',
        'privs' => array('User'),
        'links' => array(
          array('text' => 'Change Password', 'priv' => 'chgPass', 'link' => 'my_application.php?path=UserMaint.UserHtml&cmd=changePass')
        )
      ),
      array(
        'sectionTitle' => 'User Maintenance',
        'privs' => array('EditUsers'),
        'links' => array(
          array('text' => 'Edit Users', 'priv' => 'EditUsers', 'link' => 'my_application.php?path=UserMaint.UserMaintHtml'),
          array('text' => 'Edit Groups','priv' => 'EditGroups','link' => 'my_application.php?path=GroupMaint.GroupMaintHtml')
        )
      )
    );
  }

  static public function blockEntries($blockName) {}

  /**
   * Required function by framework.
   */
  static public function getModulePath() { return dirname(__FILE__); }

}












