<?php
//Created by Greg Quick
//2/7/2014

//Class definition for opening and executing queries against json encoded queries

//TODO ADD LOGGING
class queryHandler
{

  private $aQueries;
  private $bStatus;
  private $sError;

  //constructor
  //takes json file location and decodes into an object to be used later for running queries
  public function __construct($sQueryFile)
  {
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"sQueryFile[$sQueryFile] ",__LINE__);
    if (file_exists($sQueryFile) === false)
    {
      $this->bStatus = false;
      $this->sError  = "Could not Load File";
      mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"problem loading sQueryFile[$sQueryFile]",__LINE__);
    }
    else
    {
      $this->bStatus = true;
      $sFileText = file_get_contents($sQueryFile);
      //mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"JSON data from file: \n$sFileText",__LINE__);
      if (($this->aQueries = json_decode($sFileText,true)) === NULL)
      {
        $this->bStatus = false;
        $this->sError  = "Could parse JSON data";
        mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"Could not parse JSON data from file[$sQueryFile]",__LINE__);
      }
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"query file contents: ". print_r($sQueryFile,true),__LINE__);
    }
  }

  //returns the curret state of the Object
  public function getStatus()
  {
    return $this->bStatus;
  }

/*
 hhmm.. you are not actually receiving a dynamic number of arguments. You are specifically maxing at 2 with the 2nd being "mixed". 
 Could this have been accomplished with just: runQuery($sQueryName,$aQueryArguments=array());
 eg.

function runQuery($f1,$f2=array())
{
  if (count($f2) == 0)
    echo "f1[$f1] f2 is empty\n";
  else
    echo "f1[$f1] f2 has multiple params: ".print_r($f2,true)."\n";
}

echo "\n\n---------- No params\n";
runQuery("bar");
echo "\n---------- 2 params\n";
runQuery("baa",array('?userName' => 42, '&lala' => 'woo woo'));
echo "\n\n";

*/
  public function runQuery()
  {
    //retrieve dynamic number of arguments
    $iNumberOfArgs=func_num_args();
    $aArgList=func_get_args();
    $aQueryArguments = array();
    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"iNumberOfArgs[".(int)$iNumberOfArgs."] aArgList:". print_r($aArgList,true),__LINE__);
    if ($iNumberOfArgs==2)
    {
      $sQueryName      = &$aArgList[0];
      $aQueryArguments = &$aArgList[1];
    }
    else if($iNumberOfArgs==1)
    {
      $sQueryName    = &$aArgList[0];
      $aQueryArguments = array();
    }
    else
      return $this->setError("runQuery() called with the wrong number of arguments");

    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"sQueryName[$sQueryName] iNumberOfArgs[".(int)$iNumberOfArgs."] aQueryArguments:". print_r($aQueryArguments,true),__LINE__);
    if (!isset($this->aQueries[$sQueryName]) || count($this->aQueries[$sQueryName]) == 0)
    {
      mjlog(DEBUG,__METHOD__,"this->aQueries: ". print_r($this->aQueries,true),__LINE__);
      return $this->setError($sQueryName. " Query not found");
    }
    $aQuery = $this->aQueries[$sQueryName];

    // initialize Database
    $db = mj_DbPool::getDb('base');

    if ($aQuery['qryArgLen']!=sizeof($aQueryArguments))
      return $this->setError($sQueryName . " called with the wrong number of arguments " . sizeof($aQueryArguments));

    //replace wildcards in sql template
    $sql = $aQuery['qryString'];
    foreach ($aQueryArguments as $sArgName => $sArgValue)
    {
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"str_replace: sArgName[$sArgName] sArgValue[$sArgValue]",__LINE__);
      $rez = str_replace($sArgName,$sArgValue,$sql);
      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"str_replace: rez[$rez]",__LINE__);
      $sql = $rez;
    }

    mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"qryType[".$aQuery['qryType']."] sql[$sql]",__LINE__);
    switch (strtolower($aQuery['qryType']))
    {
      case 'select': return $this->runSelectQuery($db,$sql); break;
      case 'insert': return $this->runInsertQuery($db,$sql); break;
      case 'update': return $this->runUpdateQuery($db,$sql); break;
      case 'delete': return $this->runDeleteQuery($db,$sql); break;
      default: return $this->setError($aQuery['qryType'] . " is not a valid query type"); break;
    }
  }

  //returns result dataset in array
  private function runSelectQuery($db,$sql)
  {
    if(!($rez = $db->query($sql)))
      return $this->setError("Query Failed: ".$sql);
    else
    {
      $aRawData = array();
      while($row = $db->fetchArray($rez))
	$aRawData[] = $row;
      return $aRawData;
    }
  }

  //returns ID of newly inserted row
  private function runInsertQuery($db,$sql)
  {
    if(!($rez = $db->query($sql)))
      return $this->setError("Query Failed: ".$sql);
    else
      return $db->insertId();
  }
   
  //returns true if completed successfully
  private function runUpdateQuery($db,$sql)
  {
    if(!($rez = $db->query($sql)))
      return $this->setError("Query Failed: ".$sql);
    else
      return true;
  }

  //returns true if completed successfully
  private function runDeleteQuery($db,$sql)
  {
    if(!($rez=$db->query($sql)))
      return $this->setError("Query Failed: ".$sql);
    else
      return true;
  }

  //sets the most recent error status
  private function setError($sError)
  {
    mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"sError[$sError]",__LINE__);
    $this->bStatus = false;
    $this->sError = $sError;

    return false;
  }

}
?>
