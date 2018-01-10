<?php
/*******************************************************************************
 * FILE: login.php
 * DESCRIPTION:
 *   This script handles login and logout as well as tracking attendance
 *
 * DEFAULT ACTION:
 *  By default this scipt will display the login page.
 * 
 * OPTIONAL FORM VARIABLES:
 *   errmsg - upon display of the page the passed error message wil be displayed to the user
 *   cmd - Controls which action is to be performed by this script if not the default
 *       - valid cmd values are:
 *         - empty, no value - display the default login page
 *         - login - make the actual login attempt
 *           - REQUIRED ADDITIONAL FORM VARIABLES:
 *             - email - email address of the user
 *             - pass  - password for the user
 *         - logout - logs the user out by destroying his session
 *         - change_password - change the current user's password
 *           - REQUIRED ADDITIONAL FORM VARIABLES:
 *             - pass0 - original password for the user
 *             - pass1 - new password for the user
 *             - pass2 - new password repeated, must match pass1
 *         - showUsage - display the usage summary report for the current user
 *           - REQUIRED ADDITIONAL FORM VARIABLES:
 *             - usageUserId - id of the user to show the usage summary for
 *
 * OUTPUT:
 *   to stdout - displays the login page or redirects to page.php
 *******************************************************************************/

define("LGNFNAME",basename(__FILE__)); // for cleaner logging

$cmd    = isset($_REQUEST['cmd'])    ? $_REQUEST['cmd']    : "";
$errmsg = isset($_REQUEST['errmsg']) ? $_REQUEST['errmsg'] : "";

$main_tpl  = 'login.tpl.php';
$frame_tpl = 'frame_sslogin.tpl.php';

// set some special system flags so that session validation knows we are attempting login and lets us through
if ($cmd == 'login')
{
  DEFINE('MJ_IS_LOGIN_VALIDATE','1'); // special flag to shotcut session validation and allow login
  DEFINE('MJ_IS_SHOW_LOGIN',    '0'); // special flag allow login page display
}
else
{
  DEFINE('MJ_IS_LOGIN_VALIDATE','0'); // special flag to shotcut session validation and allow login
  DEFINE('MJ_IS_SHOW_LOGIN',    '1'); // special flag allow login page display
}

//$mjincfname = getenv('MJ_ROOT_PATH')."lib/mj_Logger.php";
//echo LGNFNAME." - checking file: mjincfname[$mjincfname]<br><br>\n";
//if (file_exists($mjincfname) === false)
//{
//  echo "<br><br>".LGNFNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
//  exit();
//}
//require_once($mjincfname);
$mjincfname = getenv('MJ_ROOT_PATH')."lib/mj_init.php";
if (file_exists($mjincfname) === false)
{
  echo "<br><br>".LGNFNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
  exit();
}
require_once(getenv('MJ_ROOT_PATH')."lib/mj_init.php");


mjlog(DEBUG,'login.php',"back from require-once... cmd[$cmd] is_show_login[".MJ_IS_SHOW_LOGIN."] errmsg[$errmsg]");

$LINE = 0; // variable used to show better logging line numbers

////////////////////////////////////////////////////////////////////////////////

if ($cmd == 'change_password')
{
  // 
  //
  mjlog(DEBUG,'login.php',"change password");
  
  $pass0 = isset($_POST['pass0']) ? trim($_POST['pass0']) : NULL;  // prior password, used for validation
  $pass1 = isset($_POST['pass1']) ? trim($_POST['pass1']) : NULL;  // new password
  $pass2 = isset($_POST['pass2']) ? trim($_POST['pass2']) : NULL;  // new password repeated

  try
  {
    // make sure the password is at least 6 characters long.
    if (strlen($pass1) < 6)
      { $LINE = __LINE__; throw new Exception("Password too short."); }

    // make sure the new password match
    if ($pass1 != $pass2)
      { $LINE = __LINE__; throw new Exception("Passwords do not match."); }

    // actually request the password change from the library
    if (!mj_User::$currUser->changePassword($pass0, $pass1))
      { $LINE = __LINE__; throw new Exception("Failed to change password."); }
  }
  catch (Exception $e)
  {
    // catch any errors and report accordingly
    mjlog(DEBUG,LGNFNAME,"Exception caught: ".$e->getMessage(),$LINE);
    echo "<SCRIPT language=\"JavaScript\"> alert(\"".$e->getMessage()."\"); </SCRIPT>\n";
    return false;
  }

  $cmd = 'logout'; // allow fall thru and perform a logout
  $errmsg = "Password changed. Please log in again.";
}

////////////////////////////////////////////////////////////////////////////////

if ($cmd == 'logout')
{
  mjlog(DEBUG,'login.php',"at cmd == logout");

  // request a logout.  Clear and destroy the current user session
  //
  mjlog(DEBUG,'login.php',"logout, redir to[".LOGIN_PAGE."]");
  
  if (!isset($_SESSION['userId'])) mj_redirect(LOGIN_PAGE);

  $userId = $_SESSION['userId'];
  //logOutAttendance($userId); // Added by RMS 2010/10/24 
  
  $_SESSION = array(); // Unset all of the session variables.

  // to kill the session, also delete the session cookie.
  // Note: This will destroy the session, and not just the session data!
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
              $params["path"], $params["domain"],
              $params["secure"], $params["httponly"]
             );
  }
  // Finally, destroy the session.
  session_destroy();

  mj_redirect(LOGIN_PAGE,"");
  exit;
}

////////////////////////////////////////////////////////////////////////////////

else if ($cmd == 'login' && $errmsg == "")
{
  mjlog(DEBUG,'login.php',"at cmd == login && errmsg == ''");

  // this is actual login attempt with email and pass, not just showing the login page
  // handling of the login attempt
  // upon success the user will be redirected to his home page and the script will exit
  // otherwise it falls thru to show the login page again.
  //
  if (!(isset($_REQUEST['email']) && isset($_REQUEST['pass']))) 
  {
    $errmsg = "Incomplete data, please try again";  
    mjlog(DEBUG,'login.php',"set errmsg[$errmsg]");
  }
  else 
  {
    // validate the login request
    if (($id = mj_User::validate($_REQUEST['email'],$_REQUEST['pass'])) === false)
    {
      // login FAILED: set the errmsg variable and log it
      if (defined(MJ_PASSWORD_LOGGING) && MJ_PASSWORD_LOGGING == 1)
          mjlog(NOTICE,'login.php',"Failed user login attempt: user[".$_REQUEST['email']."] pass[".$_REQUEST['pass']."]");
      else
          mjlog(NOTICE,'login.php',"Failed user login attempt: user[".$_REQUEST['email']."]");

      mj_redirect('login.php',"Failed login: Please try again or contact support.");
      exit;
    }
    else
    {
      // login SUCCESS: track the attendance and 
      // logAttendance($id); // $_REQUEST['email']); // Added by RMS 2010/10/22 
      mjlog(DEBUG,'login.php',"setting sessionID[$id] and redirecting to[".USER_HOME_PAGE."]",__LINE__);

      $_SESSION['userId'] = $id;
      $_SESSION['id'] = $id;
	  
      // Assign the userLevel to the session - deprecated
      $_SESSION['userLevel'] = "";

      // redirect to the user's home page or to the passwod change page and exit
      if (trim($_REQUEST['pass']) == 'password')
        mj_redirect('myapp.php?path=UserMaint.UserMaintHtml&cmd=changePass');
      else
        mj_redirect(USER_HOME_PAGE); 

      exit;
    }
  }
}

////////////////////////////////////////////////////////////////////////////////
//
// main section for displaying the login page.
// perform the actual page construction and send it out
//
$smarty = new mj_Smarty;

if (isset($_REQUEST['email'])) $email = $_REQUEST['email'];
else $email = "";

if ($_SESSION['userId'] > 0) mj_redirect(USER_HOME_PAGE); 

$smarty->assign('user',     mj_Config::getGeneralConfigValue('user',''));
$smarty->assign('password', mj_Config::getGeneralConfigValue('password',''));
$smarty->assign('email',    $email);
$smarty->assign('summary',  "");
$smarty->assign('errmsg',   $errmsg);
$login_html = $smarty->fetch($main_tpl);
$smarty->clear_all_assign();

$smarty->assign('userId', (isset($_SESSION['userId']) ? $_SESSION['userId'] : ""));
$smarty->assign('content', $login_html);
$smarty->assign('headerTitle',mj_Config::$ini['general']['appTitle']);
$page_html = $smarty->fetch($frame_tpl);
$smarty->clear_all_assign();

echo $page_html;

/*
curl -X POST --data "username=YOURNAME&password=YOURPASSWORD" https://account.dyn.com/entrance/
curl -X POST --data "cmd=login&email=zaijohn&pass=123test" http://shotspotz.localhost/login.php

curl -X POST --data "cmd=login&email=zaijohn&pass=123test" http://shotspotz.localhost/myapp.php?path=Location.LocationText&cmd=UploadLocationImage

curl http://example.com
*/


