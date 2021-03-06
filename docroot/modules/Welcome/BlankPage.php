<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

class BlankPage //implements mj_ModuleInterface
{
  static public function init() { }

  static public function getHeadScript($tplname,$slotname)
  {
    return "";
  }

  static public function leftMenu($tplname,$slotname)
  {
    return "";
  }

  static public function getHeadBlock($tplname,$slotname)
  {
    return "";
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
    return array();
  }

  static public function blockEntries($blockName) {}

  static public function getModulePath() { return dirname(__FILE__); }
}












