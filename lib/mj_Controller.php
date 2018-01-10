<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Controller.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

//$mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_Logger.php";
//if (!file_exists($mjincfname))
//{
//  echo "<br><br>".FNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
//  exit();
//}
//require_once($mjincfname);
$mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_init.php";
if (!file_exists($mjincfname))
{
  echo "<br><br>".FNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
  exit();
}
require_once(getenv('MJ_ROOT_PATH')."lib/mj_init.php");

/**
 * The Controller class creates and executes the requested class based on "path" and "cmd".
 *
 * URLs must be of one of two formats:
 *  ?path=<Application>.<Class>&cmd=<anything>
 * or
 *   ?cmd=<appKey>
 *   where appLey is an alias for an <Application>.<Class> that is configured in to ini['CmdHandlers']
 *
 *********************************************************************************/
class mj_Controller
{
  function __construct()
  {
    //parent::__construct();
  }

  static public function init()
  {
    foreach (mj_Config::$ini['AppBase.Includes']['php'] as $foo => $fname)
    {
      mj_require_once($fname);
    }
    foreach (mj_Config::$ini['Modules']['appModules'] as $foo => $moduleName)
    {
      mj_Config::addIncludePath(MJ_ROOT_PATH . MJ_MODULE_DIR .'/'. $moduleName);
      mj_require_once($moduleName.".php"); 
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"calling[$moduleName::init()]",__LINE__); 
      $moduleName::init();
    }
  }

  /**
   * This is the main controller class for an MVC impimentation. It examines the current request and instantiates the objects necessary to fulfill the request.
   * 
   * @return Returns an instance of the Class designated to fulfill the given request. If no handler can be found an error is thrown.
   */
  static public function factory($path,$cmd)
  {
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"path[$path] cmd[$cmd]",__LINE__); 
    //mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"_GET[".print_r($_GET,true)."]",__LINE__); 

    $parts = explode(".",$path);
    list($moduleName,$theClass) = $parts;
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"moduleName[$moduleName] theClass[$theClass]",__LINE__); 
    /*
    $numparts = count($parts);
    switch ($numparts)
    {
      case 0: $theClass = mj_Config::$ini['CmdHandlers'][$cmd];                                      break;
      case 1: $theClass = mj_Config::$ini['CmdHandlers'][($parts[0] == "" ? $parts[0] : $parts[1])]; break;
    }
    */
    if (($pos = strpos($theClass,'?')) !== false)
      $theClass = substr($theClass,0,$pos);

    $tplPath = getenv('MJ_ROOT_PATH')."ht/modules/".$moduleName."/templates";
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"moduleName[$moduleName] theClass[$theClass] tplPath[".$tplPath."]",__LINE__);
    mj_Smarty::addTemplateDir_s($tplPath);
    
    if (isset(mj_Config::$ini['TemplateDirs']['dir']))
    {
      $tpldirs = mj_Config::$ini['TemplateDirs']['dir'];
      foreach ($tpldirs as $foo => $dir)
      {
        mj_Smarty::addTemplateDir_s($dir);
      }
    }
    if ($theClass == "") { throw new Exception("Invalid command request: path"); }
 
    // add the application dir to the include_path so that its easier to include other classes required by the application. 
    mj_Config::addIncludePath(MJ_ROOT_PATH . MJ_MODULE_DIR .'/'. $moduleName);

    if (!class_exists($moduleName))
    {
      mj_require_once($moduleName.".php"); 
      $moduleName::init();
    }
    
    $fname = MJ_ROOT_PATH . MJ_MODULE_DIR .'/'. $moduleName .'/'. $theClass.".php";
    mj_require_once($fname);
    $instance = new $theClass($moduleName);
    return $instance;
  }
}














