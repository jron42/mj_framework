<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Session.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/


class mj_Session
{
  static private $sessDb = NULL;
  static private $inited = false; // protect against being init'd more than once.

  //function __construct() { }
 
  static function init($useDB=false)
  {
    // Initialize session
    //
    if (!self::$inited)
    {
      self::$inited = true;

      session_start();

      // setup a forced session timeout in the code so that we are not only relying on other means.
      //
      if (isset($_SESSION['LAST_ACTIVITY']) && MJ_SESSION_TIMEOUT > 0 && ((time() - $_SESSION['LAST_ACTIVITY']) > MJ_SESSION_TIMEOUT)) 
      {
        // last request was more than 30 minutes ago
        mjlog(NOTICE,__CLASS__.'::'.__FUNCTION__,"session timeout: time[".time()." - last[".$_SESSION['LAST_ACTIVITY']."] "
             . (time() - $_SESSION['LAST_ACTIVITY']) ." > MJ_SESSION_TIMEOUT[".MJ_SESSION_TIMEOUT."]",__LINE__);

        session_destroy();   // destroy session data in storage
        session_unset();     // unset $_SESSION variable for the runtime
        mj_redirect('login.php','NOTICE: Sorry, your session has expired. Please log in again.');
      }
      $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

      // create a new sessionID for the current session every 30 minutes to provide damage control
      // if a session is highjacked.
      //
      if (!isset($_SESSION['CREATED'])) 
      {
        $_SESSION['CREATED'] = time();
      } 
      else if (MJ_SESSION_REGEN_ID > 0 && (time() - $_SESSION['CREATED']) > MJ_SESSION_REGEN_ID) 
      {
        // session started more than MJ_SESSION_REGEN_ID minutes ago
        mjlog(NOTICE,__CLASS__.'::'.__FUNCTION__,"session regen: time[".time()." - created[".$_SESSION['CREATED']."] "
             . (time() - $_SESSION['CREATED']). " > MJ_SESSION_REGEN_ID[".MJ_SESSION_REGEN_ID."]",__LINE__);
        session_regenerate_id(true);    // change session ID for the current session an invalidate old session ID
        $_SESSION['CREATED'] = time();  // update creation time
      }

      //if ($useDB)
      //{
      //  // This function sets up the User object with a database connection
      //  if ((self::$sessDb = mj_DbPool::getDb("session")) === false)
      //  {
      //    mjlog(ERROR,__CLASS__.'::'.__FUNCTION__,'Failed to get Database connection');
      //    return false;
      //  }
      //  //else echo "self::init: sessDb[$name]\n";
      //}
    }

    // perform code to grab session control here
    return true;
  }

  /**
   * Creates a token usable in a form
   * @return string
   */
  static public function getToken()
  {
    $token = sha1(mt_rand());
    if(!isset($_SESSION['tokens']))
    {
      $_SESSION['tokens'] = array($token => 1);
    }
    else
    {
      $_SESSION['tokens'][$token] = 1;
    }
    return $token;
  }

  /**
   * Check if a token is valid. Removes it from the valid tokens list
   * @param string $token The token
   * @return bool
   */
  static public function isTokenValid($token)
  {
    if(!empty($_SESSION['tokens'][$token]))
    {
      unset($_SESSION['tokens'][$token]);
      return true;
    }
    return false;
  }


}

/*******************************************************************************
//
// this is code for saving a session into a database that has not been implimented yet
//

$conn = odbc_connect("webdb", "php", "chicken");
$stmt = odbc_prepare($conn,
      "UPDATE sessions SET data = ? WHERE id = ?");
$sqldata = array (serialize($session_data), $_SERVER['PHP_AUTH_USER']);
if (!odbc_execute($stmt, $sqldata)) {
    $stmt = odbc_prepare($conn,
     "INSERT INTO sessions (id, data) VALUES(?, ?)");
    if (!odbc_execute($stmt, $sqldata)) {
        // Something went wrong..
    }
}

    $session_data = unserialize($tmp[0]);
    if (!is_array($session_data)) {
        // something went wrong, initialize to empty array
        $session_data = array();
    }

}

$sess_conn = NULL;

function open($save_path, $session_name)
{
  //This function can be empty unless you need to open a database connection
  $sess_conn = mysql_connect('localhost', 'mysql_user', 'mysql_password');
  if (!$sess_conn)die('Could not connect: ' . mysql_error());
  return(true);
}
function close()
{
  //This function can be empty unless you need to close a database connection
  mysql_close($sess_conn);
  return(true);
}
function read($id)
{
  $result = mysql_query("select sess_data from sessions where id = '$id'",$sess_conn);
  if ($result)
    if ($row = mysql_fetch_row($result))return $row[0];
  return "";
}
function write($id, $sess_data)
{
  $cleandata = mysql_real_escape_string($sess_data);
  $updateSQL = "update sessions set sess_data = '$cleandata' last_update = now() where id = '$id'";
  $insertSQL = "insert into sessions (id,last_update,sess_data) values ('$id',now(),'$cleandata'";
  if (mysql_query($updateSQL,$sess_conn))       return true;
  else if (mysql_query,($insertSQL,$sess_conn)))return true;
  return false;
}
function destroy($id)
{
  mysql_query($sess_conn,"delete from sessions where id = '$id'",$sess_conn);
  return(true);
}
function gc($maxlifetime)
{
  $max_age = 2;
  mysql_query("delete from sessions where datediff(now(),last_update) > $max_age",$sess_conn);
  return true;
}

session_set_save_handler("open", "close", "read", "write", "destroy", "gc");
session_start();


*/









