<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_misc.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

define("MJMISCFNAME",basename(__FILE__));

function hasAccess($page_name)
{
  //$db = mj_DbPool::getDb('base');

  return mj_User::$currUser->isAllowed($page_name);
/*
  // Assign the current user's userLevel to a local variable
  $userLevel = mj_User::$currUser->id;  //$_SESSION['userLevel'];
  //mjlog(SECURITY,PGFNAME,$username . ' has user level access ' . $userLevel);
 
  // If the user is "admin", bypass the page access checking
  if ($userLevel == "Admin")
    return true;
  else {
    // Issue a SQL query
    $sql = "select count(*) as cnt from UserLevels, UserLevelsAccess "
         . "where UserLevels.userLevel = UserLevelsAccess.userLevel "
         . "and UserLevels.shortDesc = '$userLevel' and pageAccess = '$page_name'";
    if (!($rez = $db->query($sql)))
      throw new Exception("Query failed for $sql");
    $row = $db->fetchArray($rez);

    if ($row['cnt'] > 0)
      return true;
    else
      return false;
  }
*/
}

function mj_UrlFormvarsToArray($str)
{
  $rez = array();
  $pairs = explode('&', $str);
  $ii = 0;
  while ($ii < count($pairs)) 
  {
    list($name,$value) = explode('=', $pairs[$ii]);
    $rez[$name] = $value;
    $ii++;
  }
  return $rez;
}

////////////////////////////////////////////////////////////////////////////////

function mj_getenv($name)
{
  return isset($_SERVER[$name]) ? $_SERVER[$name] : NULL;
}

function mj_isset($thing,$default='')
{
  if (isset($thing)) return $thing;
  return $default;
}

////////////////////////////////////////////////////////////////////////////////

/**
 * use this version to go straight to the db otherwise use mj_htget
 */
function mjhtget($name,$defval=NULL)
{
  if (isset($_REQUEST[$name]))
    if(is_array($_REQUEST[$name]))
      return mj_Db::safeStr(trim(implode(",",$_REQUEST[$name])),false);
    else
      return mj_Db::safeStr(trim($_REQUEST[$name]),false);
  else  
    return ($defval === NULL ? NULL : (($defval === true || $defval == 1) ? "" : $defval));
    //return $emptyAsStr ? "" : NULL;
}

function mj_htget($name,$defaultVal="")
{
  if (isset($_REQUEST[$name]))
    return trim($_REQUEST[$name]);
  return $defaultVal;
}

function mj_getValue($var,$default)
{
  return ((isset($var)) ? $var : $default);
}

////////////////////////////////////////////////////////////////////////////////

/**
 * stub function for a future library function for cleaning html output
 * 
 * @param string $str string to be formatted
 * 
 * @return string safe string for output to browser 
 */
function mj_safeHtml($str)
{
  return htmlspecialchars($str);
}

////////////////////////////////////////////////////////////////////////////////

/**
 * convenience function to generate the javascript for popping up an myalert() as found in mj_lib.js
 * 
 * @param string $msg message to be displayed in alert box
 * @param boolean $echo if true results to stdout
 * 
 * @return string string containing the javascript code for an alert
 */
function mj_htEchoJsAlert($msg,$echo=true)
{
  $js = "<script type=\"text/javascript\">myalert(\"$msg\");</script>\n";
  if ($echo) echo $js;
  return $js;
}

////////////////////////////////////////////////////////////////////////////////

/**
 * primary function for redirecting to another page using a 301 redirect. 
 * 
 * @param string $url page to redirect to
 * @param string $errmsg error/success message to be (optionally) displayed by the subsequent page.
 * @param array $params assoc array of form data to be attached to the given URL
 */
function mj_redirect($url,$errmsg=NULL,$params=NULL)
{
  mjlog(DEBUG,MJMISCFNAME.':'.__FUNCTION__,"redirecting: url[$url] errmsg[$errmsg]",__LINE__);
  mjlog(DEBUG,MJMISCFNAME.':'.__FUNCTION__,mj_trace(),__LINE__);

  $first = true;
  if ($errmsg != NULL)
  {
    $url .= ($first?'?':'&') . 'errmsg='. rawurlencode($errmsg);
    $first = false;
  }
  if (is_array($params))
  {
    foreach ($params as $key => $value)
    {
      $url .= ($first?'?':'&') . rawurlencode($key) .'='. rawurlencode($value);
      if ($first) $first = false;
    }
  }
  mjlog(DEBUG,MJMISCFNAME.':'.__FUNCTION__,"redirecting: url[$url]",__LINE__);
  if (0) 
  {
    exit;
  }
  header("HTTP/1.1 301 Moved Permanently"); 
  header("Location: $url"); 
}

/**
 *
 **/
function mj_redirectTop($url,$errmsg=NULL)
{
  mjlog(DEBUG,MJMISCFNAME.':'.__FUNCTION__,"redirecting: url[$url] errmsg[$errmsg]",__LINE__);
  mjlog(DEBUG,MJMISCFNAME.':'.__FUNCTION__,mj_trace(),__LINE__);

  if ($errmsg != NULL)
  {
    $url .= '?errmsg='. rawurlencode($errmsg);
  }
  echo "<html><head><script>top.document.location='$url';</script></head><body></body></html>";
}

////////////////////////////////////////////////////////////////////////////////

// String EnCrypt + DeCrypt function

function mj_encryptLow($str,$ky='')// key can not have spaces
{
  if ($ky == '') return $str;

  $ky = $ky . KEY_PHRASE;
  //echo "encrypt: key[$ky]\n";
  if (strlen($ky) < 8) return false; // key error

  $klen = strlen($ky) < 32 ? strlen($ky) : 32;
  $k = array();
  for ($ii=0; $ii < $klen; $ii++)
    $k[$ii] = ord($ky{$ii}) & 0x1F;

  $jj = 0;
  for($ii=0; $ii < strlen($str); $ii++) {
    $e = ord($str{$ii});
    $str{$ii} = $e&0xE0 ? chr($e^$k[$jj]) : chr($e);
    $jj++;
    $jj = $jj == $kl ? 0 : $jj;
  }
  return $str;
}

function mj_hex2bin($str)
{
    //echo "hex2bin: str[$str]\n";
    $bin = "";
    $i = 0;
    do {
        //echo $str[$i] . $str[($i + 1)] . "\n";
        $bin .= chr(hexdec($str[$i].$str[($i + 1)]));
        $i += 2;
    } while ($i < strlen($str));
    return $bin;
}

function mj_bin2hex2($str)
{
    //echo "bin2hex2: str[$str]\n";
    $hex = "";
    $i = 0;
    do {
        $hex .= sprintf("%02x", ord($str{$i}));
        $i++;
    } while ($i < strlen($str));
    return $hex;
}

function mj_encrypt($str,$key,$enc)
{
  //echo "convert: enc[".(int)$enc."] ". ($enc ? "Encrypt\n" : "Decrypt\n");
  //echo "convert: str[$str] key[$key]\n";
  if (!$enc) $str = mj_hex2bin($str);
  $str = mj_encryptLow($str,INSTALLATION_TYPE.$key);
  if ($enc) return mj_bin2hex2($str);
  return $str;
}






