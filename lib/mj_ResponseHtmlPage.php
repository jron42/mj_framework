<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_ResponseHtmlPage.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

mj_require_once("mj_ResponseInterface.php");
mj_require_once("mj_ModuleInterface.php");

class mj_ResponseHtmlPage extends mj_ResponseInterface
{
  protected $headerJsFiles  = array(); // names of js files (no path)  - This should be set by the child class
  protected $headerCssFiles = array(); // names of css files (no path) - This should be set by the child class
  protected $headerScript = "";        // block of script to be put in the header section - can include js and/or css
  protected $footerScript = "";        // block of script to be put at the bottom of the document
  protected $readyScript  = "";        // block of script to be put in the document ready section
  protected $htmlcode     = "";        // html code that will be put into the content area of the framework.

  protected $path         = "";
  protected $cmd          = "";
  protected $subcmd       = "";
  protected $errmsg       = "";
  protected $currUserObj  = ""; 

  function __construct($moduleName) 
  {
    mjlog(DEBUG,__METHOD__,"moduleName[$moduleName]",__LINE__);
    parent::__construct($moduleName);
  }

  /**
   * To be overridden by child classes so that the appropriate content type is generated. 
   * For most html pages the default is correct so this function need do nothing.
   */
  function outputResponseHeader()
  {
    //header('Content-Type: application/json');
    header("content-type: text/html; charset=utf-8");
  }

  /**
   * The main function used to build the output. Override this method and pake sure to call the parent and then add your own logic.
   */
  public function execute($path,$cmd,$subcmd,$errmsg)
  {
    mjlog(DEBUG,__METHOD__,"\n",__LINE__);
    mjlog(DEBUG,__METHOD__,"++++++++++++++ execute path[$path] cmd[$cmd] subcmd[$subcmd] errmsg[$errmsg]",__LINE__);

    $this->path   = $path;
    $this->cmd    = $cmd;
    $this->subcmd = $subcmd;
    $this->errmsg = $errmsg;

    //parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass
    //
    // do your stuff
    //
    $foo = "bar";
  } 

  /**
   * This function sets some common variables that should be in almost every template. 
   **/
  protected function setBaseTemplateValues(&$smarty)
  {
    $smarty->assign('userId',     mj_User::$currUser->id);
    $smarty->assign('username',   mj_User::$currUser->fullName);
    $smarty->assign('userName',   mj_User::$currUser->fullName);
    $smarty->assign('userStatus', mj_User::$currUser->status);
    $smarty->assign('userPrivs',  mj_User::$currUser->getAllPrivs());
    $smarty->assign('path',       $this->path);
    $smarty->assign('cmd',        $this->cmd);
    $smarty->assign('subcmd',     $this->subcmd);
    $smarty->assign('errmsg',     $this->errmsg);
    $smarty->assign('controllerApp', mj_Config::$ini['AppBase']['controllerApp']);
  }

  protected function getTemplatePath()
  {
    //return dirname(__FILE__).'/';
    $parts = explode(".",$this->path);
    return $parts[0] . "/";
  }

  protected function simpleTemplateInclude(&$smarty,$tpl)
  {
    $this->setBaseTemplateValues($smarty);
    $smarty->clear_all_assign();
  }

  /**
   * scan the configured in classes for menu items by calling their navigationEntries() functions
   **/
  private function getNavigationEntries($blockName)
  {
    $navItems = array();
    foreach (mj_Config::$ini['Modules']['appModules'] as $foo => $moduleName)
    {
      $arr = $moduleName::navigationEntries($blockName);
      $navItems = array_merge_recursive($navItems,$arr);
    }

  }

  private function processSlotFunction($tplClass,$tplName,$slotname,$funcStr)
  {
    mjlog(DEBUG,__METHOD__,"Processing tplClass[$tplClass] tplName[$tplName] slotname[$slotname] funcStr[$funcStr]",__LINE__);

    if (trim($funcStr) == '') return ' ';
    $rez = "";
    $parts = explode('|',$funcStr);
    if (!isset($parts[0]) || !isset($parts[0])) return "";

    mjlog(DEBUG,__METHOD__,"Processing command: parts: ".print_r($parts,true),__LINE__);
    switch ($parts[0])
    {
      case 'inline': $rez = $parts[1]; break;  // just dump whatever the string was
      case 'eval':   eval($parts[1]);  break;  // directly evaluate the string as php code

      case 'function': // call a function - string should be with <class>.<function> or just <function>
      {
        $fparts = explode('.',$parts[1]);
        mjlog(DEBUG,__METHOD__,"Processing fparts: ".print_r($fparts,true),__LINE__);
	switch (count($fparts))
	{
	  case 0: $rez = ""; break;
	  case 1: $rez = $fparts[0]($tplClass,$slotname); break;
	  case 2: $rez = $fparts[0]::$fparts[1]($tplClass,$slotname); break;
	  //case 3: $rez = $fparts[0]();
	}
	break;
      }

      case 'file': // read and return the contents of the given file
      {
        $fname = MJ_ROOT_PATH. $parts[1];
	if (file_exists($fname))
	  return file_get_contents($fname);
	break;
      }

      case 'template': // grab and dump the give template
      {
        $smarty = new mj_Smarty;
	$this->setBaseTemplateValues($smarty);
	$rez = $this->fetchTpl($smarty,$parts[1]);
	unset($smarty);
	break;
      }

      default: break;
    }
    switch ($parts[0])
    {
      case 'inline':
      case 'eval':
    //case 'function':
        mjlog(DEBUG,__METHOD__,"rez[$rez]",__LINE__);
        break;
    }
    return $rez;
  }

  protected function processFramework(&$frameSmarty)
  {
    $smarty = new mj_Smarty;
    mjlog(DEBUG,__METHOD__,"user privs: ". print_r(mj_User::$currUser->getAllPrivs(),true),__LINE__);

    // iterate thru the different templates and process each into its slot
    foreach (mj_Config::$ini['AppBase.UI']['tpl'] as $tplClassName => $tplData)
    {
      mjlog(DEBUG,__METHOD__,"Processing tplClassName[$tplClassName]",__LINE__);

      if ($tplClassName == '' || $tplClassName == 'framework') continue; // process the slots first, skip the main template for now
      if (!is_array($tplData)) continue;

      //$tplData = mj_Config::$ini['AppBase.UI']['tpl'][$tplClassName];
      
      $tplName = isset($tplData['name']) ? $tplData['name'] : '';  // get the template name we are going to process, bail if not set
      if ($tplName == '') continue;

      $parentSlot = isset($tplData['parentSlot']) ? $tplData['parentSlot'] : ''; // no where to put this so bail
      if ($parentSlot == '') continue;

      if ($tplClassName == 'head') 
      {
        $moduleJs = 'modules/' . $this->moduleName .'/js/'. $this->moduleName .'.js';
        if (file_exists($moduleJs))
	  $this->headerJsFiles[] = $moduleJs;
        $headJsFiles = array_merge(array_values(mj_Config::$ini['AppBase.Includes']['js']),$this->headerJsFiles);
        $smarty->assign('headJsFiles', $headJsFiles);

        $moduleCss = 'modules/' . $this->moduleName .'/css/'. $this->moduleName .'.css';
        if (file_exists($moduleCss))
	  $this->headerCssFiles[] = $moduleCss;
        $headCssFiles = array_merge(array_values(mj_Config::$ini['AppBase.Includes']['css']),$this->headerCssFiles);
        $smarty->assign('headCssFiles', $headCssFiles);

        $smarty->assign('headScript',       $this->headerScript);
        $smarty->assign('readyScript',      $this->readyScript);
        $smarty->assign('globalReadyScript',$this->processSlotFunction($tplClassName,$tplName,'globalReadyScript',$tplData['globalReadyScript']));
      }

      if ($tplClassName == 'footer') 
      {
        $smarty->assign('footerScript', $this->footerScript);
      }

      if (isset($tplData['rawScript']) && (($filler = $tplData['rawScript']) != '')) // handle a special case slot intended for raw script/css
      {
        $smarty->assign('rawScript',$this->processSlotFunction($tplClassName,$tplName,'rawScript',$filler));
      }

      if (isset($tplData['slots'])) 
      {
        foreach ($tplData['slots'] as $slotname => $filler)
	{
          if ($slotname != '' && $filler != "")
          {
            $smarty->assign($slotname,$this->processSlotFunction($tplClassName,$tplName,$slotname,$filler));
          }
	}
      }

      mjlog(DEBUG,__METHOD__,"about to fetchTpl tplName[$tplName]",__LINE__);
      $this->setBaseTemplateValues($smarty);
      $str = $this->fetchTpl($smarty,$tplName);

      mjlog(DEBUG,__METHOD__,"about to frameSmarty->assign parentSlot[$parentSlot]",__LINE__);
      $frameSmarty->assign($parentSlot,$str);
      $smarty->clear_all_assign();
    }
    unset($smarty);

    //$frameSmarty->assign('headerTitle',mj_Config::$ini['general']['appTitle']);
  }

  private function fetchTpl(&$smarty,$name)
  {
    try
    {
      $rez = $smarty->fetch($name);
    }
    catch (Exception $e)
    {
      mjlog(ERROR,__METHOD__,"Exception caught: ".$e->getMessage(),__LINE__);
      $template_dir = $smarty->getTemplateDir();
      mjlog(ERROR,__METHOD__,"Problem loading template[$name] config:\n".print_r($template_dir,true),__LINE__);
      exit();
    }
    return $rez;
  }

  public function dump($errmsg)
  {
    $smarty = &$this->smarty;
    mjlog(DEBUG,__METHOD__,"\n",__LINE__);
    mjlog(DEBUG,__METHOD__,"++++++++++++++ dump",__LINE__);

    // develop and set the generic page framework and dump in the content from above
    //
    //$func = $page."_top";
    //if (function_exists($func)) $func($db,$smarty);
    //$jsdoc = "js/class_".$cmd.".js";
    //if (!file_exists($jsdoc)) $jsdoc = "";

    $ftpl = "";
    if (($ftpl = trim(mj_Config::$ini['AppBase.UI']['tpl']['framework']['name'])) == "")
    {
      mjlog(ERROR,__METHOD__,"Problem loading template[$ftpl] config:\n".print_r(mj_Config::$ini,true),__LINE__);
      
      throw new Exception("Problem loading template[$ftpl]"); 
      exit();
    }
    mjlog(DEBUG,__METHOD__,"loading template[$ftpl]",__LINE__);

    if ($errmsg != "") $this->errmsg = $errmsg;

    $smarty->clear_all_assign();
    $this->setBaseTemplateValues($smarty);
    $smarty->assign('UI_SESSION_TIMEOUT',(defined('MJ_SESSION_TIMEOUT_UI')?MJ_SESSION_TIMEOUT_UI:0));
    $this->processFramework($smarty);

    $smarty->assign('content', $this->htmlcode);

    $page_html = $this->fetchTpl($smarty,$ftpl);

    $smarty->clear_all_assign();

    $this->outputResponseHeader();
    echo $page_html;
  }
}

