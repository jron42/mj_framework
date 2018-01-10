<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("mj_ResponseHtmlPage.php");
mj_require_once("Welcome.php");

class ErrorPage extends mj_ResponseHtmlPage
{
  function __construct($moduleName)
  {
    mjlog(DEBUG,__METHOD__,"moduleName[$moduleName]",__LINE__);
    parent::__construct($moduleName);
  }

  static public function leftMenu($tplname,$slotname)
  {
    return "";
  }

  function execute($path,$cmd,$subcmd,$errmsg)
  {
    parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass
    
    $this->smarty->assign('errmsg', $errmsg);
    
    $tpl = "ErrorPage.tpl.php";
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"fetching template [$tpl]",__LINE__);
    $this->htmlcode = $this->smarty->fetch($tpl);

    $this->smarty->clear_all_assign();
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

}












