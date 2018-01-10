<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("mj_ResponseHtmlPage.php");
mj_require_once("Welcome.php");

class WelcomeHtml extends mj_ResponseHtmlPage
{
  function __construct($moduleName) 
  {
    mjlog(DEBUG,__METHOD__,"moduleName[$moduleName]",__LINE__);
    parent::__construct($moduleName);

    $this->headerJsFiles[] = MJ_MODULE_DIR ."/ExampleHello/js/WelcomeHtml.js";

    $this->headerScript = "<script>writeConsole('made it to some include script in the WelcomeHtml class');</script>\n";
  }

  static public function leftMenu($tplname,$slotname)
  {
    return "<br>WelcomeHtml.leftMenu: tplname[$tplname] slotname[$slotname]'<br>\n";
  }


  function execute($path,$cmd,$subcmd,$errmsg)
  {
    parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass

    // do your stuff and fill up the htmlcode variable
    //
    $somestuff = mj_Config::$ini["cmd.html_hello"]['hi_string'];
    $content_html = "<br><br><center><font color=red>MADE IT: class WelcomeHtml</font><br><br><br>cfg stuff for [cmd.html_hello]: $somestuff</center>";

    $smarty = &$this->smarty;
    $smarty->addTemplateDir(ExampleHello::getModulePath());
 
    $this->setBaseTemplateValues($smarty);

    //mjlog(ERROR,__METHOD__,"mj_Config::ini: ". json_encode(mj_Config::$ini,JSON_PRETTY_PRINT),__LINE__);
    //$stuff = "<pre>".json_encode(mj_Config::$ini,JSON_PRETTY_PRINT)."</pre>";
    //$smarty->assign('stuff', $stuff);


    $tpl = "WelcomeHtml.tpl.php";
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"fetching template [$tpl]",__LINE__);
    $this->htmlcode = $smarty->fetch($tpl);

    $smarty->clear_all_assign();
  } 
}

