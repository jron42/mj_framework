<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

abstract class mj_ResponseInterface
{
  protected $moduleName = "";
  protected $smarty = null;

  function __construct($moduleName)
  {
    $this->moduleName = $moduleName;

    static $registered_classes = array();

    $theClass = get_called_class();
    logit(__METHOD__,"moduleName[$moduleName] theClass[$theClass]",__LINE__);

    if (!in_array($theClass,$registered_classes))
    {
      //mj_Config::addClassConfig($moduleName,$theClass);
      $registered_classes[] = $theClass;
    }

    $this->smarty = new mj_Smarty;
    $tplPath      = array();
    $tplPath[]    = MJ_ROOT_PATH."ht/templates";
    $tplPath[]    = $moduleName::getModulePath()."/templates";
    mjlog(DEBUG,__METHOD__,"moduleName[$moduleName] tplPath[".print_r($tplPath,true)."]",__LINE__);
    $this->smarty->addTemplateDir($tplPath);
  }

  // hook into the main loop
  abstract public function execute($path,$cmd,$subcmd,$errmsg);

  abstract public function outputResponseHeader();

  abstract public function dump($errmsg);
}

