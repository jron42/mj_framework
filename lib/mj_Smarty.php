<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Smarty.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

require_once('SmartyBC.class.php');

/*
 * This class allows for some global Smarty configuration changes.
 *
 * @package mj_lib
 */


class mj_Smarty extends SmartyBC
{
  static $templateDirs = array(
    MJ_ROOT_PATH .'ht/templates',
    MJ_ROOT_PATH .'ht/templates/formBuilder'
  );  
  /*
   * This constructor will set the delimiters so that Javascript code doesn't screw up and setup for language inclusion.
   */
  function __construct()
  {
    $this->left_delimiter  = MJ_SMARTY_LEFT_DELIM;
    $this->right_delimiter = MJ_SMARTY_RIGHT_DELIM;

/*
    $cfgdirs = $this->getConfigDir();
    //$this->setConfigDir(array_merge(array('./templates/lang')),$cfgdirs);
    $this->addConfigDir('./templates/lang');
*/
    foreach (self::$templateDirs as $tdir)
      parent::addTemplateDir($tdir);

//echo "--- template dirs 2:"; var_dump($this->getTemplateDir());
//echo "\n\n";
//exit();

    
    parent::__construct();
  }

  static function addTemplateDir_s($dirname, $key = NULL, $isConfig = false)
  {
    if (count(self::$templateDirs) == 0)
    {
      $smarty = new mj_Smarty;
      self::$templateDirs = $smarty->getTemplateDir();
      unset($smarty);
    }
    
    $newdir = (strncmp(MJ_ROOT_PATH,$dirname,strlen(MJ_ROOT_PATH)) == 0) ? $dirname : (MJ_ROOT_PATH .$dirname);
    if (!in_array($newdir,self::$templateDirs))
      self::$templateDirs[] = $newdir;
  }

  function addTemplateDir($dirname, $key = NULL, $isConfig = false)
  {
    if (count(self::$templateDirs) == 0)
       self::$templateDirs = $this->getTemplateDir();
    
    if (!is_array($dirname)) $dirname = array($dirname);
    foreach ($dirname as $dirnamestr)
    {
      $newdir = (strncmp(MJ_ROOT_PATH,$dirnamestr,strlen(MJ_ROOT_PATH)) == 0) ? $dirnamestr : (MJ_ROOT_PATH .$dirnamestr);
      if (!in_array($newdir,self::$templateDirs))
      {
        self::$templateDirs[] = $newdir;
        parent::addTemplateDir($newdir, $key, $isConfig);
      }
    }
  }
  /*
   * execute the template construction and return the resuults. Parameters are the same as from the parent function.
   * This function was overridden so that context could be pushed to all templates.
   */
  public function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false)
  {
    if (defined('MJ_SITE_NAME')) $this->assign('siteName',MJ_SITE_NAME);
    $this->assign('context',mj_Config::getContext());

    try
    {
      return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
    }
    catch (Exception $e)
    {
      $msg = $e->getMessage();
      if (stripos($msg,"Unable to load template") !== false) 
      {
        $dirs = $this->getTemplateDir();
        mjlog(ERROR,__METHOD__,"Exception caught: $msg \ngetTemplateDir: ". print_r($dirs,true),__LINE__);
      }
      throw $e;
    }
  }
}


