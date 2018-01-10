<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_Config.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

if (!defined('MJ_ROOT_PATH')) define('MJ_ROOT_PATH',getenv('MJ_ROOT_PATH'));

$mjincfname = MJ_ROOT_PATH."/3rdParty/Ini_Struct.php";
if (file_exists($mjincfname) === false)
{
  echo "<br><br>".LGNFNAME." - ERROR: Required include file does not exist! file[$mjincfname]<br><br>";
  exit();
}
require_once(MJ_ROOT_PATH.'/3rdParty/Ini_Struct.php');

////////////////////////////////////////////////////////////////////////////////
// system defines
//

if ((($mj_installType = getenv("MJ_INSTALL_TYPE")) === false) || $mj_installType == "")
{
  echo "ERROR: MJ_INSTALL_TYPE NOT SET !!!!<br>\n";
  exit;
}
if (!defined('MJ_INSTALL_TYPE')) define('MJ_INSTALL_TYPE',$mj_installType);

////////////////////////////////////////////////////////////////////////////////
// If we want to do any logging we have to use a hamstrung logger since configuration
// hasn't been loaded yet
//

function logit($where,$str,$line=0)
{   
  if (function_exists("mjlog"))
  {
    if (!isset($where))
    {
      static $first = true;
      if (!$first) return;
      $first = false;
      mjlog(DEBUG,__FUNCTION__,"where unset". mj_trace(),__LINE__);
      return;
    }
    mjlog(DEBUG,$where,$str,$line=0);
    return;
  }

  static $fp = NULL;

  if (0)
  {
    echo $where,($line?"($line)":''),' ',$str,"<br>\n";
  }
  if (1)
  {
    if ($fp == NULL)
      if (!($fp = fopen('/tmp/zzz_config.log','a'))) { echo "ERROR opening log file: /tmp/zzz_config.log<br>\n"; exit; }
    fprintf($fp,"%s %s %s\n",$where,($line?"($line)":''),$str);
    fflush($fp);
  }
}

////////////////////////////////////////////////////////////////////////////////

class mj_Config
{
  static public  $ini     = NULL;    // an array of config data
  static public  $cfg     = NULL;    // holds a pointer to the main Config object instance. Only 1 per app
  static public  $cfgData = array(); // an array of config data
  static private $dbData  = array(); // an array of db config data
  static private $context = array(); // an array of db config data
  static private $cfgFile = NULL;    // full path and name of the top level config file.
  static private $cfgPath = NULL;    // full path and name of the top level config file.

  function __construct($fname)
  {
    $addini  = array();
    self::$cfgFile = $fname;
    self::$cfgPath = dirname($fname);

    logit(__METHOD__,"checking for TOP TOP level config file[$fname]",__LINE__);
    $baseini = Ini_Struct::parse($fname, true);  // read in the top level ini file

    // process the [defines] section into actual php defines
    //logit(__METHOD__,"about to process ConfigIncludesIni: ". print_r($baseini['ConfigIncludesIni'],true),__LINE__);
    if (isset($baseini['ConfigIncludesIni']['ini']))
    {
      //logit(__METHOD__,"baseini ConfigIncludesIni count[".count($baseini['ConfigIncludesIni']['ini'])."] ".print_r($baseini['ConfigIncludesIni'],true),__LINE__);
      foreach ($baseini['ConfigIncludesIni']['ini'] as $name => $value)
      {
        $fname = self::$cfgPath .'/'. $value;
       //logit(__METHOD__,"processing include config file[$value] cfgPath[".self::$cfgPath."] fullpath[$fname]",__LINE__);
        if (file_exists($fname))
       {
         if (($tmpini  = Ini_Struct::parse($fname, true)) !== false)
         {
            //logit(__METHOD__,"fname[$fname] tmpini: ". print_r($tmpini,true),__LINE__);
           $baseini = array_replace_recursive($baseini,$tmpini);
         }
          else logit(__METHOD__,"ERROR: Invalid ini config file[$value]",__LINE__);
       }
       else logit(__METHOD__,"ERROR: Unable to include config file[$value]",__LINE__);
      }
    }
    //logit(__METHOD__,"baseini: ". print_r($baseini,true),__LINE__);
    //exit();

    // process the [defines] section into actual php defines
    $ii = 0;
    if (isset($baseini['defines']))
    {
      logit(__METHOD__,"baseini count[".count($baseini['defines'])."] ".print_r($baseini['defines'],true),__LINE__);
      foreach ($baseini['defines'] as $name => $value)
      {
        try
       {
          //logit(__METHOD__,((int)$ii)." defining[$name] = [$value]",__LINE__); 
          //if (defined($name)) logit(__METHOD__,((int)$ii)." ERROR: name[$name] ALREADY DEFINED!!!!!",__LINE__);
          //if ($name != 'MJ_LOG_LEVEL')
         {
            define($name,$value);
            logit(__METHOD__,((int)$ii)." defined[$name]",__LINE__);
         }
          $ii++;
       }
       catch (Exception $e)
       {
         logit(__METHOD__,"ERROR defining[$name] = [$value] errmsg: ".$e->getMessage(),__LINE__);
       }
      }
    }
    //logit(__METHOD__,"MJ_LOG_LEVEL[".MJ_LOG_LEVEL."]",__LINE__);
    logit(__METHOD__,"------- finished defines - processed[".(int)$ii."]",__LINE__);

    // load extra ini files based on config if they exist
    //
    if (isset($baseini['AppBase.AdditionalConfig']['ini']))
    {
      foreach ($baseini['AppBase.AdditionalConfig']['ini'] as $name => $value)
      {
        $inifname = MJ_ROOT_PATH. $value;
        logit(__METHOD__,"checking for top level config file[$inifname]",__LINE__);
        if (file_exists($inifname))
        {
          $addini = Ini_Struct::parse($inifname, true);
          //$inidata = Ini_Struct::parse($inifname, true);
          //$addini  = array_merge($addini,$inidata);
        }
        echo 
          logit(__METHOD__,"missing config file[$inifname]",__LINE__);
      }
    }

    // load extra module level ini files if they exist
    //
    if (isset($baseini['Modules']['appModules']))
    {
      foreach ($baseini['Modules']['appModules'] as $name => $value)
      {
        // process ini files that exist in the module's code tree
        //
        $inifname = MJ_ROOT_PATH. MJ_MODULE_DIR .'/'. $value .'/'. $value . ".ini.php";
        logit(__METHOD__,"checking for MODULE - CODE level config file[$inifname]",__LINE__);
        if (file_exists($inifname))
        {
          $inidata[$value]  = Ini_Struct::parse($inifname, true);
          //logit(__METHOD__,"MODULE level config[$inifname]: ". print_r($inidata,true),__LINE__);
          $addini = array_replace_recursive($addini,$inidata);
        }
        else 
          logit(__METHOD__,"no CODE config file found for [$inifname]",__LINE__);

        // process ini files that exist in project's config directory
        //
        $inifname = self::$cfgPath .'/'. $value . ".ini.php";
        logit(__METHOD__,"checking for MODULE - PROJECT level config file[$inifname]",__LINE__);
        if (file_exists($inifname))
        {
          $inidata[$value]  = Ini_Struct::parse($inifname, true);
          //logit(__METHOD__,"MODULE level config[$inifname]: ". print_r($inidata,true),__LINE__);
          $addini = array_replace_recursive($addini,$inidata);
        }
        else 
          logit(__METHOD__,"no PROJECT config file found for[$inifname]",__LINE__);

        // process json files that exist in the module's code tree as well as those in the config tree
        //
        logit(__METHOD__,"about to process json - addini[$value]: ". print_r($addini,true),__LINE__);
        $jdata = array();
        if (isset($addini[$value]['json']['file']))
        {
          foreach ($addini[$value]['json']['file'] as $foo => $fname)
          {
            logit(__METHOD__,"checking json value[$value] foo[$foo] - fname[$fname]",__LINE__);
            for ($ii=0; $ii < 2; $ii++)
           {
              $jfname = MJ_ROOT_PATH. MJ_MODULE_DIR .'/'. $value .'/'. $fname;
             if ($ii == 1)
                $jfname = self::$cfgPath .'/'. basename($fname);
              logit(__METHOD__,"checking[".(int)$ii."] json file[$jfname]",__LINE__);
              if (file_exists($jfname))
              {
                logit(__METHOD__,"json file[$jfname] ",__LINE__);
                $fdata = file_get_contents($jfname,true);
                if ($fdata !== false)
                {
                  if (($jtmp = json_decode($fdata,true)) !== NULL)
                    $jdata = array_replace_recursive($jdata,$jtmp);
                  else
                    logit(__METHOD__,"ERROR: unable to decode json in file[$jfname]",__LINE__);
                }
              }
              else logit(__METHOD__,"json file does not exist[$jfname] ",__LINE__);
           }
          }
          if (count($jdata) > 0)
          {
            $addini[$value] = array_replace_recursive($addini[$value],$jdata);
          }
        }
        else logit(__METHOD__,"[json] section has no value",__LINE__);

        // process json files that exist in the module's code tree as well as those in the config tree
        //
        $jdata = array();
             $moduleName = $value;
        if (isset($addini[$moduleName]['subclasses']['class']))
        {
          foreach ($addini[$moduleName]['subclasses']['class'] as $foo => $className)
          {
            $tmpini = self::addClassConfig($moduleName,$className,$addToIni = false);
            if (count($tmpini) > 0)
            {
              $addini[$moduleName][$className] = $tmpini;
            }
          }
        }
        else logit(__METHOD__,"[subclasses] section has no value",__LINE__);
      }
    }

    //logit(__METHOD__,"adding config: ". print_r($addini,true),__LINE__);
    self::$ini = array_replace_recursive($baseini,$addini);
    
    logit(__METHOD__,"\n-------------------------------------\nself::ini: ". print_r(self::$ini,true),__LINE__);
    logit(__METHOD__,"\n-------------------------------------\n--- finished initial config\n-------------------------------------\n\n");
  }


  static public function addClassConfig($moduleName,$theClass,$addToIni=false)
  {
    logit(__METHOD__,"moduleName[$moduleName] theClass[$theClass]",__LINE__);
    $inidata1   = array();
    $inidata2   = array();
    $inidata3   = array();
    $inifname   = MJ_ROOT_PATH. MJ_MODULE_DIR .'/'. $moduleName .'/'. $theClass . ".ini.php";
    logit(__METHOD__,"checking for CLASS level config file[$inifname] in the code area",__LINE__);
    if (file_exists($inifname))
    {
      $inidata1 = Ini_Struct::parse($inifname, true);
      //logit(__METHOD__,"module level config[$inifname]: ". print_r($inidata,true),__LINE__);
    }
    else 
      logit(__METHOD__,"missing config file[$inifname]",__LINE__);

    $inifname = self::$cfgPath .'/'. $moduleName .'.'. $theClass . ".ini.php";
    logit(__METHOD__,"checking for CLASS level config file[$inifname] in the project area",__LINE__);
    if (file_exists($inifname))
    {
      $inidata2 = Ini_Struct::parse($inifname, true);
      //logit(__METHOD__,"module level config[$inifname]: ". print_r($inidata,true),__LINE__);
    }
    else
      logit(__METHOD__,"missing config file[$inifname]",__LINE__);

    $inidata = array_replace_recursive($inidata1,$inidata2);

    // process json files that exist in the module's code tree as well as those in the config tree
    //
    $jdata = array();
    if (isset($inidata['json']['file']))
    {
      foreach ($inidata['json']['file'] as $foo => $fname)
      { 
        logit(__METHOD__,"checking json foo[$foo] - fname[$fname]",__LINE__);
        for ($ii=0; $ii < 2; $ii++)
        {
          $jfname = MJ_ROOT_PATH. MJ_MODULE_DIR .'/'. $moduleName .'/'. $fname;
          if ($ii == 1)
            $jfname = self::$cfgPath .'/'. basename($fname);
          logit(__METHOD__,"checking[".(int)$ii."] json file[$jfname]",__LINE__);
 
          if (file_exists($jfname))
          {
            $fdata = file_get_contents($jfname,true);
            if ($fdata !== false)
            {
              if (($jtmp = json_decode($fdata,true)) !== NULL)
                $jdata = array_replace_recursive($jdata,$jtmp);
              else
                logit(__METHOD__,"ERROR: unable to decode json in file[$jfname]",__LINE__);
            }
          }
        }
      }
      if (count($jdata) > 0)
      {
        $inidata = array_replace_recursive($inidata,$jdata);
      }
    }

    logit(__METHOD__,"inidata: ". print_r($inidata,true),__LINE__);
    if ($addToIni)
    {
      $inidata3[$moduleName][$theClass] = $inidata;
      self::$ini = array_replace_recursive(self::$ini,$inidata3);
      logit(__METHOD__,"self::ini: ". print_r(self::$ini,true),__LINE__);
    }
    return $inidata;
  }

  static public function init()
  {
    /*
    echo "\n\n";
    echo "**************************************************************************\n";
    echo "************************************************************************** self::ini\n";
    print_r(self::$ini);
    echo "**************************************************************************\n";
    echo "**************************************************************************\n";
    echo "\n\n";
    */
    $dbsection = self::$ini['db.'.MJ_INSTALL_TYPE];

    foreach (explode('|',$dbsection['connections']) as $cname)
    {
      $cfg = $dbsection[$cname];

      self::$dbData[$cname] = array(
        "server" => $cfg['server'],
        "user"   => $cfg['user'],
        "pass"   => $cfg['pass'],
        "defDb"  => $cfg['defDb'],
        "useEncryption" => $dbsection['useEncryption'],
        "encToken"      => $dbsection['encToken']
      );

      foreach (explode('|',$cfg['aliases']) as $alias)
        self::$dbData[$alias] = &self::$dbData[$cname];

      if (isset(self::$ini['general.'.MJ_INSTALL_TYPE]))
      {
        self::$ini['general'] = self::$ini['general.'.MJ_INSTALL_TYPE];
        self::$cfgData        = &self::$ini['general.'.MJ_INSTALL_TYPE];
      }
      else
      {
        if (isset(self::$ini['general.default']))
        {
          self::$ini['general'] =  self::$ini['general.default'];
          self::$cfgData        = &self::$ini['general'];
        }
      }
    }

/*
 * I think, not supported any more "special" section
 *

    // handle special items and traspose to arrays from JSON or XML
    //
    logit(__METHOD__,"\n\n--------------------\n------------------\nprocess special sections\n\n",__LINE__);

    if (isset(self::$ini['special']))
    {
      logit(__METHOD__,"in 'special' section",__LINE__);
      foreach (explode('|',self::$ini['special']['json']) as $cat)
      {
       if (($sect = &self::getIniSection($cat)) !== NULL)
          $sect = "foo";
       {
          list($section) = explode('.',$cat);
         if ($section == "image")
            logit(__METHOD__,"cat[$cat] section[".print_r($sect,true)."]",__LINE__);

         if (!is_array($sect) && $sect != "")
         {
            logit(__METHOD__,"process section to json",__LINE__);
           $sect = json_decode($sect,true);
       }
          else logit(__METHOD__,"DO NOT process section to json",__LINE__);

          logit(__METHOD__,"after json: sect[".print_r($sect,true)."]",__LINE__);
       }
      }
      foreach (explode('|',self::$ini['special']['simple_arrays']) as $cat)
      {
       if (($sect = &self::getIniSection($cat)) !== NULL)
       {
          //list($section,$item) = explode('.',$cat);
          //if (isset(self::$ini[$section][$item]))
          //  self::$ini[$section][$item] = explode('|',self::$ini[$section][$item]);
          $sect = explode('|',$sect);
       }
      }
      if (isset(self::$ini['special']['json']) && self::$ini['special']['json'] != '')
      {
        foreach (explode('|',self::$ini['special']['json']) as $cat)
        {
          logit(__METHOD__,"cat[$cat]",__LINE__);
          $keys = explode('.',$cat);
          switch(count($keys))
           {
           case 1: self::$ini[$keys[0]] = json_decode(self::$ini[$keys[0]],true); break;
           case 2: self::$ini[$keys[0]][$keys[1]] = json_decode(self::$ini[$keys[0]][$keys[1]],true); break;
           case 3: self::$ini[$keys[0]][$keys[1]][$keys[2]] = json_decode(self::$ini[$keys[0]][$keys[1]][$keys[2]],true); break;

           case 0:
           default: logit(__METHOD__,"wrong number of keys[".(int)count($keys)."]",__LINE__); brea;
         }
        }
      }
      if (isset(self::$ini['special']['simple_arrays']) && self::$ini['special']['simple_arrays'] != '')
      {
        foreach (explode('|',self::$ini['special']['simple_arrays']) as $cat)
        {
          logit(__METHOD__,"cat[$cat]",__LINE__);
          $keys = explode('.',$cat);
          switch(count($keys))
         {
           case 1: self::$ini[$keys[0]] = explode('|',self::$ini[$keys[0]]); break;
           case 2: self::$ini[$keys[0]][$keys[1]] = explode('|',self::$ini[$keys[0]][$keys[1]]); break;
           case 3: self::$ini[$keys[0]][$keys[1]][$keys[2]] = explode('|',self::$ini[$keys[0]][$keys[1]][$keys[2]]); break;

           case 0:
           default: logit(__METHOD__,"wrong number of keys[".(int)count($keys)."]",__LINE__); brea;
         }
        }
      }
    }
    else logit(__METHOD__,"ERROR: no 'special' section",__LINE__);
*/
    if (1)
    {
      //echo "ini: "; print_r(mj_Config::$ini);
      //echo "dbData: "; print_r(mj_Config::$dbData);

      if (isset(mj_Config::$ini['image']))
        logit(__METHOD__,"ini: ". print_r(mj_Config::$ini['image'],true));
      //logit(__METHOD__,"ini: ". print_r(mj_Config::$ini,true));
    }
  }

  /**
   * retrieve a section based on a . delimited string such as image.size.large
   *
   * @param string $name . delimited string such as image.size.large
   *
   * @return mixed returns the value string or array if found or NULL of not
   */
  static function getIniSection($name)
  {
/*
    logit(__METHOD__,"name[$name]",__LINE__);
    $parts = explode('.',$name);
    $first = array_shift($parts);
    $sect  = self::$ini[$first];
    foreach($parts as $part)
    {
      logit(__METHOD__,"foreach part[$part]",__LINE__);
      if (isset($sect[$part]))
        $sect = &$sect[$part];
      else
      {
        $sect == NULL;
        break;
      }
    }
    logit(__METHOD__,"val[$sect]",__LINE__);
    return $sect;
*/
  }

  function __get($name)
  {
    return self::$ini[$name];
  }

  /**
   * low level convenience function for returning config information
   *
   * @return string|mixed|NULL 
   *     if $name is null then the config array is returned
   *     if the config array is unset or the desired value is unset then NULL is returned
   *     if name is NULL the config array is returned
   *     if name is not NULL the config string for the named item is returned
   */
  static private function _getConfig($cat,$name,$def=NULL)
  {
    if ($name === NULL)
      return (isset(self::$ini[$cat]) ? self::$ini[$cat] : ($def !== NULL ? $def : NULL));
    return isset(self::$ini[$cat][$name]) ? self::$ini[$cat][$name] : ($def !== NULL ? $def : NULL);
  }

  /**
   * low level convenience function for returning general config information
   */
  static public function getGeneralConfigValue($name=NULL,$def=NULL)
  {
    $ini = self::$ini;
    $gen = self::$ini['general'];

    $rez = self::_getConfig('general', $name, $def); 
    //logit(__METHOD__,"returning: general config $name[$name] default[$def] rez[$rez]". print_r(self::$ini['general'],true),__LINE__);
    return $rez;
  }
  static public function general($name=NULL) { return self::getGeneralConfigValue($name); } // deprecated

  /**
   * low level convenience function for returning image config information
   */
  static public function image($name=NULL)
  {
    return self::_getConfig('image', $name); 
  }

  static public function db($usage="base") 
  { 
    logit(__METHOD__,"usage[$usage]",__LINE__);
    //echo "db: usage[$usage]\n";
    //logit(__METHOD__,print_r(self::$dbData,true),__LINE__);
    //echo "db: [".Config::$dbData[$usage]['server']."]\n"; 
    return self::$dbData[$usage];
  }

  static function addIncludePath($new_path)
  {
    logit(__METHOD__,"adding new php include path entry: new_path[$new_path]",__LINE__);
    $old_include_path = ini_get('include_path');
    ini_set('include_path', "$old_include_path:$new_path");
  }

  static function getContext($name=null)
  {
    if ($name === null) return self::$context;
    else                return self::$context[$name];
  }

  static function setContext($name,$value)
  {
    return self::$context[$name] = $value;
  }
}


