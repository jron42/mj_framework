<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.3 $
 * $Id: mj_Logger.php,v 1.3 2016/12/04 14:55:56 jmorgan Exp $
 ********************************************************************************/

////////////////////////////////////////////////////////////////////////////////
// logging specific defines
//

//if (function_exists("mjlog")) return; // prevent this one from being called twice just in case.

date_default_timezone_set('America/New_York');

$mj_startTime = microtime(true); // used in case we are timing queries

define("MJLOGFNAME",basename(__FILE__));

define('CRIT',          '1');
define('ERROR',         '2');
define('SECURITY',      '3');
define('WARNING',       '4');
define('NOTICE',        '5');
define('DEBUG_HIGH',    '6');
define('DEBUG',         '7');
define('MJ_MAX_LOG_LVL','7');

define('LOG_ECHO','0');

$_mj_logpath = "";
if ((($_mj_logpath = getenv("MJ_LOG_PATH")) === false) || $_mj_logpath == "")
  define('MJ_LOG_PATH','./');
else
  define('MJ_LOG_PATH',$_mj_logpath);

$_mj_logfile = "";
if ((($_mj_logfile = getenv("MJ_LOG_NAME")) === false) || $_mj_logfile == "")
  define('MJ_LOG_NAME','mj_ht_default.log');
else
  define('MJ_LOG_NAME',$_mj_logfile);

define('MJ_LOGFILE',MJ_LOG_PATH . MJ_LOG_NAME);

$MJ_NO_LOG = 0; // default is to log - 0 == logging, 1 == no logging

////////////////////////////////////////

function mjlog($level,$where,$str,$line=0)
{
  global $php_cmdline, $MJ_NO_LOG;
  static $fp = NULL;
  static $levels = array('unknown','CRIT','ERROR','SECURITY','WARNING','NOTICE','DEBUG_HIGH','DEBUG');
  static $first  = true;

  $userId = isset($_SESSION['userId']) ? $_SESSION['userId'] : "X";

  $log_level = mj_Config::getGeneralConfigValue('logLevel',5);  // defined('MJ_LOG_LEVEL') ? MJ_LOG_LEVEL : MJ_MAX_LOG_LVL;
  if (defined('MJ_LOG_USER_ID') && (((int)$userId) > 0) && (MJ_LOG_USER_ID == $userId))
  {
    $log_level = MJ_MAX_LOG_LVL;
  }
  //$log_level = 5;

  //echo "log_level[$log_level] MJ_NO_LOG[". (int)$MJ_NO_LOG ."]";

  if ($level <= $log_level)
  {
    if ($MJ_NO_LOG == 1 && $level > 3) $str = "--- MJ_NO_LOG --- LOGGING TURNED OFF --- ";

    if ($level < WARNING) $str .= "\ntrace: ". mj_trace();

    $logit = true;
    if ($level > NOTICE)
    {
      $parts = explode(':',$where);
      $logClasses = mj_Config::getGeneralConfigValue('logClasses',array());
      if (count($logClasses) > 0 && !in_array($parts[0],$logClasses))
        $logit = false;
    }

    //echo " logit[". (int)$logit ."]";

    if (LOG_ECHO || $php_cmdline == 1)
      echo date("Y-m-d H:i:s"),' ',$levels[((int)$level)],' ',$where,($line?"($line)":''),' ',$str,(isset($php_cmdline)?'<br>':''),"\n";
    elseif ($logit)
    {
      if ($fp == NULL)
        if (!($fp = fopen(MJ_LOGFILE,'a'))) { echo "ERROR opening log file[". MJ_LOGFILE ."]<br>\n"; exit; }
      fprintf($fp,"%s %8s %s:u-%d:rq-%s %s %s\n",date("Y-m-d H:i:s"),$levels[((int)$level)],$where,$userId,mj_getRequestID(),($line?"($line)":''),$str);
      fflush($fp);
    }
  }
  //echo "<br>";
}

function mj_logUsage()
{
  $peak      = memory_get_peak_usage(true);
  $mem       = ($peak / 1024 / 1024);
  $memthresh = mj_Config::getGeneralConfigValue('logMemThresholdMB',98);

  global $mj_startTime;
  $endTime  = microtime(true);
  $execTime = $endTime - $mj_startTime;
  $timethresh = mj_Config::getGeneralConfigValue('logTimeThresholdSec',3.0);

  //mjlog(WARNING,__FUNCTION__,"peak[$peak] mem[$mem] memthresh[$memthresh] mj_startTime[$mj_startTime] endTime[$endTime] execTime[$execTime] timethresh[$timethresh]",__LINE__);
  //mjlog(WARNING,__FUNCTION__,"mem > memthresh = $mem > $memthresh = ". (($mem > $memthresh)?'true':'false'));
  //mjlog(WARNING,__FUNCTION__,"execTime > timethresh = $execTime > $timethresh = ". (($execTime > $timethresh)?'true':'false'));

  if ($mem > $memthresh || $execTime > $timethresh)
    mjlog(NOTICE,__FUNCTION__,sprintf("Peak mem: $mem MB - Exec Time: %.5f - URL: %s",$execTime,$_SERVER['REQUEST_URI']). "\n". print_r($_REQUEST,true),__LINE__);
}


/**
 * return a somewhat formatted backtrace for purposes of logging
 * 
 * @param integer $level not used
 * 
 * @return string string containing a backtrace formatted for logging
 */
function mj_trace( $stackLevel=-1, $exact=false )
{
  $output = "";
  $trace = debug_backtrace();
  //foreach($trace as $level)
  for ($ii=0; $ii < count($trace); $ii++)
  {
    if ($exact && $ii != $stackLevel) continue;
    if (!$exact && $stackLevel >= 0 && $ii > $stackLevel) break;
    $level  = $trace[$ii];
    $file   = $level['file'];
    $line   = $level['line'];
    //$object = $level['object'];
    //if (is_object($object)) { $object = get_class($object); }
    $output .= "trace: line $line of $file\n";
  }

  return $output;
}
//echo "before trace<br>\n";
//echo mj_trace();
//exit();

function mj_traceLogFrom()
{
  // from:trace: line 493 of /Users/jmorgan/Sites/zai/loc/nap/ht/submit.php
  $rez = '';
  $line = trim(mj_trace(2,true));
  $parts = explode(' ',$line);
  //print_r($parts);
  if (isset($parts[2]) && isset($parts[4]))
  {
    $fname = basename($parts[4]);
    $rez = $fname .':'. $parts[2];
  }
  return $rez;
}

/**
 * build a (hopefully) unique ID for each request to they can be 
 */
function mj_getRequestID()
{
  static $id = NULL;
  if ($id === NULL)
  {
    $id = substr("". md5(time() ." ". rand()),0,10);
  }
  return $id;  
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

function mj_require_base($fname,$func)
{
/*
  //$mjincfname = getenv('MJ_ROOT_PATH')."/lib/mj_init.php";
  if (!file_exists($fname))
  {
    echo "<br><br>".MJLOGFNAME." - ERROR: Required include file does not exist! file[$fname] pwd[".getcwd()."]<br><br>";
    echo "<pre>\n". mj_trace() ."\n\n</pre><br><br>";
    mjlog(ERROR,__FUNCTION__,"Required include file does not exist! file[$fname]" . "\ntrace: ". mj_trace(),__LINE__);
    exit();
  }
  mjlog(DEBUG,__FUNCTION__,"loading file: require_once[$fname]",__LINE__);
  //$func($fname);

  mjlog(DEBUG,__FUNCTION__,"checking include file[$fname]",__LINE__); 
  if (($rez = include_once($fname)) != 1)
  {
    echo "<br><br>".MJLOGFNAME." - ERROR: Required include file not found! file[$fname] <br>pwd[".getcwd()."]<br><br>";
    echo "include_path ". get_include_path() ."\n\n<br><br>";
    echo "<pre>\n". mj_trace() ."\n\n</pre><br><br>";
    mjlog(ERROR,__FUNCTION__,"Required include file does not exist! file[$fname]" 
                         . "\ninclude_path: ". get_include_path() 
                         . "\ntrace: ". mj_trace(),__LINE__);
    exit();
  }
*/
  mjlog(DEBUG,__FUNCTION__,"checking include file[$fname]",__LINE__); 
  if (($rez = stream_resolve_include_path($fname)) === false)
  {
    echo "<br><br>".MJLOGFNAME." - ERROR: Required include file not found! file[$fname] <br><br>\n";
    echo "include_path ". get_include_path() ."<br><br>\n";
    echo "<pre>\n". mj_trace() ."\n\n</pre><br><br>\n";
    mjlog(ERROR,__FUNCTION__,"Required include file does not exist! file[$fname]" 
                         . "\ninclude_path: ". get_include_path() 
                         . "\ntrace: ". mj_trace(),__LINE__);
    exit();
  }
  mjlog(DEBUG,__FUNCTION__,"checking include file[$rez]",__LINE__); 
  if ($func == "require") include($rez);
  else                    include_once($rez);

  return $rez;
}
function mj_require($fname)      { return mj_require_base($fname,"require"); }
function mj_require_once($fname) { return mj_require_base($fname,"require_once"); }

////////////////////////////////////////////////////////////////////////////////
/*
mjlog(DEBUG,'MJLOGFNAME',"\n\n============================================================\n"
                        .    "============================================================\n"
                        .    "============================================================\n\n");
*/
$a = 1;



