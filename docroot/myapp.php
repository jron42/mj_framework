<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

date_default_timezone_set('America/New_York');

/*

http://apl2core.localhost/my_application.php?path=ExampleHello.HelloHtml
http://apl2core.localhost/my_application.php?path=ExampleHello.HelloJson

php my_application.php "path=ExampleHello.HelloHtml&cmd=html_hello"

*/
define("FNAME",basename(__FILE__));

try
{
  $mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_Controller.php";
  if (!file_exists($mjincfname))
  {
    echo "<br><br>".FNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
    exit();
  }
  require_once($mjincfname);
  
  //mjlog(DEBUG,FNAME,"_GET[cmd] = [".$_GET['cmd']."]",__LINE__);
  mjlog(DEBUG,FNAME,"_GET[".print_r($_GET,true)."]",__LINE__);

  $path = mjhtget('path', mj_Config::$ini['AppBase']['landingPage']);
  list($module,$theClass) = explode(".",$path);

  $cmd    = mjhtget('cmd',   ''); //'json_hello'); // NULL);
  $subcmd = mjhtget('subcmd','');
  $errmsg = mjhtget('errmsg','');
  
  mjlog(DEBUG,FNAME,"----------- start of actual page building ------------- cmd[$cmd] subcmd[$subcmd] uid[".(int)mj_User::$currUser->id."]");

  $thing = mj_Controller::init();                 // preform some generic setup based on config
  mjlog(DEBUG,FNAME,"--- 1 ---",__LINE__);
  $thing = mj_Controller::factory($path,$cmd);    // initialize everything - get correct output type
  mjlog(DEBUG,FNAME,"--- 2 ---",__LINE__);
  $thing->execute($path,$cmd,$subcmd,$errmsg);    // execute the application content for the callback
  mjlog(DEBUG,FNAME,"--- 3 ---",__LINE__);
  $thing->dump($errmsg);                          // build framework and dump the results
  mjlog(DEBUG,FNAME,"----------- finished! page DUMPED! ------------- cmd[$cmd] subcmd[$subcmd] uid[".(int)mj_User::$currUser->id."]");
}
catch (Exception $e)
{
  // this is only for catching gross errors and should never be executed
  mjlog(DEBUG,FNAME.':'.__FUNCTION__,"Exception caught: ".$e->getMessage(),__LINE__);
  mj_logUsage();
  mj_redirect(MJ_ERROR_PAGE,$e->getMessage());
}
mj_logUsage();

