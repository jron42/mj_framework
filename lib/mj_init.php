<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_init.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

//
// initialze configuration
//
//echo "in init.php<br>\n";
//
//include('../../cgidump.php');

error_reporting(E_ALL ^ E_DEPRECATED);

$mj_root_path = getenv('MJ_ROOT_PATH');
$mj_config    = getenv('MJ_CONFIG_FILE'); // "/Users/john/Sites/foo/lib/example.ini.php"; // getenv('MJ_CONFIG_FILE');

if ($mj_config == '' || !file_exists($mj_config))
{
  echo "\n\n<br><br>\nERROR: Main config file not found. file[$mj_config]\n<br><br>\n";
  exit();
}

define('MJ_ROOT_PATH',$mj_root_path);

$php_cmdline  = getenv("MJ_PHP_CMDLINE");
if ($php_cmdline == 1)
  echo "init.php: php_cmdline: including mj_root_path[$mj_root_path]  mj_config[$mj_config] php_cmdline[$php_cmdline]<br>\n";

//
// include the base config file and initialize the app's configuration
//
$mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_Config.php";
if (!file_exists($mjincfname))
{
  echo "<br><br>".FNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
  exit();
}
require_once(MJ_ROOT_PATH."lib/mj_Config.php");

mj_Config::$cfg = new mj_Config($mj_config);
mj_Config::init();

date_default_timezone_set(MJ_DEF_TIMEZONE);

//
// include and initialize the Logging system
//
$mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_Logger.php";
if (!file_exists($mjincfname))
{
  echo "<br><br>".FNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
  exit();
}
require_once($mjincfname);

$php_lib_paths = mj_Config::$cfgData["php_lib_paths"];
mj_Config::addIncludePath($mj_root_path.':'.$mj_root_path.'ht'.':'.$mj_root_path.'lib'.':'.$php_lib_paths);

////////////////////////////////////////////////////////////////////////////////
// load special libraries
//

if (MJ_USE_SMARTY) 
{
  mj_Config::addIncludePath(MJ_ROOT_PATH.'3rdParty/Smarty/libs');
  mj_require_once('mj_Smarty.php');
}

if (defined('MJ_USE_FILE_UPLOADER') && MJ_USE_FILE_UPLOADER) 
{
  require_once('mj_File2.php');
}

/*
if (defined('MJ_USE_IMAGES') && MJ_USE_IMAGES) 
{
  require_once('mj_Image.php');
  mj_Image::setControls(mj_Config::image());
}
*/

////////////////////////////////////////////////////////////////////////////////
// load the miscelaneous function lib which inludes the logging system and start logging
//
mj_require_once('lib/mj_misc.php');
mjlog(DEBUG,'init.php',"\n\n============================================================\n");
if (defined('MJ_TEST_CONFIG')) return;
mjlog(DEBUG,'init.php','_ENV: ' .print_r($_ENV,true), __LINE__);
mjlog(DEBUG,'init.php','_POST: '.print_r($_POST,true),__LINE__);
mjlog(DEBUG,'init.php','_GET: ' .print_r($_GET,true), __LINE__);
mjlog(DEBUG,'init.php','_COOKIE: ' .print_r($_COOKIE,true), __LINE__);

if ($php_cmdline != 1)
{
  mjlog(DEBUG,'init.php','script:  '. $_SERVER['SCRIPT_NAME'],__LINE__); 
  mjlog(DEBUG,'init.php','request: '. $_SERVER['REQUEST_URI'],__LINE__); 
  mjlog(NOTICE,'init.php','REMOTE_ADDR INCOMING_IP: '. $_SERVER['REMOTE_ADDR'], __LINE__);
}
mjlog(DEBUG,'init.php','include: '. ini_get('include_path'),__LINE__); 


////////////////////////////////////////////////////////////////////////////////
// initialize the database system
//
if ((!defined('MJ_USE_DB')) || (defined('MJ_USE_DB') && MJ_USE_DB)) 
{
  mj_require_once('lib/mj_Db.php');
  mj_Db::init(getenv("MJ_DB_ENC_TOKEN"));
  mj_DbPool::init();
  if (defined('MJ_TEST_DB')) return;
}

////////////////////////////////////////////////////////////////////////////////
// initialize the shared memory system if required
//
mjlog(DEBUG,'init.php','MJ_SHARED_LIB: ['.(defined('MJ_SHARED_LIB') ? MJ_SHARED_LIB : 'undefined').']',__LINE__);
if (defined('MJ_SHARED_LIB') && MJ_SHARED_LIB != "")
{
  mj_require_once(MJ_SHARED_LIB);
  if (defined('MJ_TEST_SHARED')) return;
}
if (defined('MJ_TEST_SHARED')) return;

////////////////////////////////////////////////////////////////////////////////
// Initialize session
// - set some timeouts and session controls
// - setup a forced session timeout in the code so that we are not only relying on other means.
// - create a new sessionID for the current session every 30 minutes to provide damage control
//   if a session is highjacked.
// - MJ_USE_SESSIONS comes from config while MJ_FORCE_SESSION is put in the code to override it
//
if ((defined('MJ_USE_SESSIONS') && !MJ_USE_SESSIONS) && !defined('MJ_FORCE_SESSION')) return;

if ($php_cmdline != 1)
{
  mj_require_once('lib/mj_Session.php');
  mj_Session::init();
  mj_Session::init(MJ_DB_SESSIONS);
  mjlog(DEBUG,'init.php','session started',__LINE__); 
  mjlog(DEBUG,'init.php','_SESSION: ' .print_r($_SESSION,true), __LINE__);
}

if (defined('MJ_REQUIRE_LOGIN') && !MJ_REQUIRE_LOGIN) 
{
  mjlog(DEBUG,'init.php','MJ_REQUIRE_LOGIN is set. Returning before including User class',__LINE__); 
  return;
}

////////////////////////////////////////////////////////////////////////////////
// Initialize current User
//
$userClass = mj_Config::$ini['AppBase']['userClass'];
mjlog(DEBUG,'init.php','userClass[$userClass]',__LINE__); 

mj_require_once('lib/mj_User'.$userClass.'.php');

if (mj_User::init() === false) 
{ 
  mjlog(CRIT,'init.php:','FAILED call to mj_User::init()',__LINE__); 
  mj_redirect(MJ_ERROR_PAGE,'ERROR: Could not connect to Database. Please try again later.'); 
  exit;
}

if (mj_User::$currUser != NULL)
{
  mjlog(CRIT,'init.php:','Startup logic error! mj_User::currUser != NULL',__LINE__); 
  mj_redirect(MJ_ERROR_PAGE,'ERROR: Startup logic error! Please try again later.'); 
  exit;
}
mjlog(DEBUG,'init.php','if (!mj_User::currUser) - ie. currUser not set',__LINE__); 

////////////////////////////////////////////////////////////////////////////////
// load base libraries
//
mj_require_once('mj_ResponseInterface.php');
mj_require_once('mj_ResponseHtmlPage.php');
mj_require_once('mj_ResponseJson.php');
mj_require_once('mj_ResponseText.php');

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
// finished includes
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// check to see if this is being included from a commanline script and process accordingly
//
if ($php_cmdline == "1")
{
  // This is a commandline script
  // fake a session and validate the script user then return so the script can execute.
  //
  mjlog(DEBUG,'init.php','MJ_PHP_CMDLINE == 1',__LINE__);

  $uid = 0;
  $_SESSION['userId'] = $uid; 
  if (($uid = mj_User::validate(CMDLINE_USER,CMDLINE_PASS)) === false) // ok..  check the hard coded command line user..
  {
    mjlog(ERROR,'init.php',"Failed to validate command line user",__LINE__);
    exit;
  }
  $_SESSION['userId'] = $uid; 
  mjlog(DEBUG,'init.php',"php_cmdline user validated uid[$uid]",__LINE__); 

  //mjlog(DEBUG,'init.php','php_cmdline argc[$argc] argv: '.print_r($argv,true),__LINE__); 
  if ($_SERVER["argc"] > 1)
  {
    mjlog(DEBUG,'init.php',"php_cmdline postvars[".$_SERVER["argv"][1]."]",__LINE__); 
    $postvars = mj_UrlFormvarsToArray($_SERVER["argv"][1]);
    foreach ($postvars as $name => $value)
    {
      $_GET[$name]     = $value;
      $_POST[$name]    = $value;
      $_REQUEST[$name] = $value;
    }
    //mjlog(DEBUG,'init.php',"php_cmdline _GET[".print_r($_GET,true)."]",__LINE__); 
    //exit();
  }
  return;
}

////////////////////////////////////////////////////////////////////////////////
// check for a validated user session existing..  look to see if the session's userId is set
//
if (isset($_SESSION['userId']) && ((int)$_SESSION['userId']) > 0)
{
  // session exists so setup a User object with the given ID and return so page can display
  // this is the only valid continuation point for an application page other than login
  //
  mjlog(DEBUG,'init.php','session exists, using current session',__LINE__); 
  mj_User::$currUser = new mj_User($_SESSION['userId']);
  mjlog(DEBUG,'init.php','  -- session exists, created new mj_User based on session user ID',__LINE__); 
  mj_UserBase::$currUser->fetchBase(true,true);
  mjlog(DEBUG,'init.php','  -- session exists, completed fetchBase',__LINE__); 
  mj_UserBase::$currUser->appSpecificInit();
  mjlog(DEBUG,'init.php','  -- session exists, completed appSpecificInit',__LINE__); 

  mjlog(DEBUG,'init.php','  -- session exists, returning',__LINE__); 
  return;
}
mjlog(DEBUG,'init.php','session does NOT exist, falling thru',__LINE__); 

// ok, no session exists so must (better) be a login request of some kind.
//
if ((defined('MJ_SKIP_VALIDATION') && MJ_SKIP_VALIDATION == 1) || (defined('MJ_IS_SHOW_LOGIN') && MJ_IS_SHOW_LOGIN))
{
  // shortcut to allow login page to display
  // is_show_login is only set by login.php
  //
  mjlog(DEBUG,'init.php','IS_SHOW_LOGIN == true',__LINE__); 
  return; 
}

////////////////////////////////////////////////////////////////////////////////
// validate a login attempt and redirect back to login with errmsg if incomplete data is provided
//
if (!(defined('MJ_IS_LOGIN_VALIDATE') && MJ_IS_LOGIN_VALIDATE && isset($_REQUEST['email']) && isset($_REQUEST['pass']))) 
{ 
  // only allowable thing at this point is an actual login attempt, so email and pass better be set.
  // is_login_validate is only set by login.php when cmd == 'login'
  //
  mjlog(DEBUG,'init.php','login attempt does not have all valid data',__LINE__); 
  mjlog(DEBUG,'init.php','login attempt data: _REQUEST: '.print_r($_REQUEST,true),__LINE__); 
  mjlog(DEBUG,'init.php','login attempt data: _SESSION: '.print_r($_SESSION,true),__LINE__); 
  mj_redirect(LOGIN_PAGE,'ERROR: login attempt does not have all valid data'); 
  exit; 
}

////////////////////////////////////////////////////////////////////////////////
// at this point the only valid action should be login validation
// so if we have a valid oogin request then return so the calling page can complete
//
$_mjcmd = mjhtget('cmd');
if ($_mjcmd && $_mjcmd == 'login')
{
  mjlog(NOTICE,'init.php','dropping through to allow validation by login.php',__LINE__); 
  return;
}

////////////////////////////////////////////////////////////////////////////////
// We should never get here.
//
mjlog(DEBUG,'init.php','Invalid request',__LINE__); 
mj_redirect(MJ_ERROR_PAGE,'ERROR: Invalid request'); 
exit; 

