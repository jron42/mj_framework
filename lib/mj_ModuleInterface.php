<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_ModuleInterface.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

interface mj_ModuleInterface
{
  static public function init();

  /**
   * return an array of navigation items.
   * this array should be of the form array("group", array("user display text","link target","priv string")) where:
   *   - "group"             = menu item grouping - can be an empty string in which case there will still be a grouping
   *   - "user display text" = what will be displayed to the user
   *   - "link target"       = a URL
   *   - "priv string"       = a rights string to be tested before display
   **/
  static public function navigationEntries($blockName);

  /**
   * return a block of html to be placed into the named navigation block (ie. a div in the nav area)
   */
  static public function blockEntries($blockName);

  /**
   * return the base path to the current module (used for various include directive such as template path)
   */
  static public function getModulePath();
}

