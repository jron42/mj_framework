<?php

define("SUMFNAME",basename(__FILE__));

////////////////////////////////////////////////////////////////////////////////
/// summary_body is the function you need to edit.Y
////////////////////////////////////////////////////////////////////////////////

/*
insert UsersAttendance (userId,logDt,logInTm,logOutTm) values
  (1,'2010-11-11','20:01:00','20:06:00'),
  (1,'2010-11-11','21:01:00','21:06:00');

insert UsersAttendance (userId,logDt,logInTm) values
  (1,'2010-11-11','23:30:00'),

select SEC_TO_TIME(sum(SUBTIME(logOutTm,logInTm))) from UsersAttendance;

select SEC_TO_TIME(sum(TIME_TO_SEC(logOutTm)-TIME_TO_SEC(logInTm))) from UsersAttendance;

*/

function summary_body($db,&$smarty,$userId=0) 
{ 
  global $page,$cmd,$subcmd,$errmsg;

  if ($userId == 0) $userId = $_SESSION['userId'];

  $thedate  = isset($_REQUEST['thedate']) ? trim($_REQUEST['thedate']) : "";
  $datestr  = date('Y-m-d');
  $datedstr = date('m/d/Y');
  if ($thedate != "")
  {
    if (stripos($thedate,'-'))
    {
      $parts = explode("-",$thedate);
      $datestr  = $thedate; // yyyy-mm-dd
      $datedstr = $parts[1] ."/". $parts[2] ."/". $parts[0];
    }
    else
    {
      $parts = explode("/",$thedate); // mm/dd/yyyy
      $datedstr  = $thedate; // yyyy-mm-dd
      $datestr = $parts[2] ."/". $parts[0] ."/". $parts[1];
    }
  }

  $sql = "SELECT logOutTm, logInTm, SUBTIME(logOutTm,logInTm) as duration "
       . "FROM UsersAttendance WHERE userId = $userId and logDt = '$datestr' ORDER BY logInTm";
  if (!($rez = $db->query($sql))) 
  {
    //mjlog(ERROR,SUMFNAME,"Query failed: $query_count",__LINE__);
    mjlog(ERROR,SUMFNAME,"Query failed: ".$db->errno().":".$db->error(),__LINE__);
    return;
  }
  $summaryList = array();
  while ($row = $db->fetchArray($rez))
  {
    mjlog(DEBUG,SUMFNAME,"logInTm[".$row['logInTm']."] logOutTm[".$row['logOutTm']."] duration[".$row['duration']."]",__LINE__);
    $summaryList[] = array('logInTm' => substr($row['logInTm'],0,8), 'logOutTm' => substr($row['logOutTm'],0,8), 
                           'duration' => substr($row['duration'],0,8));
  }

  $sql = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(logOutTm)-TIME_TO_SEC(logInTm))) as duration "
       . "FROM UsersAttendance WHERE userId = $userId and logDt = '$datestr' and logOutTm > logInTm group BY logDt";
  if (!($rez = $db->query($sql))) 
  {
    //mjlog(ERROR,SUMFNAME,"Query failed: $query_count",__LINE__);
    mjlog(ERROR,SUMFNAME,"Query failed: ".$db->errno().":".$db->error(),__LINE__);
    return;
  }
  $row = $db->fetchrow($rez);

  // display dates because users are complaining.
  //
  if (($rez1 = $db->query("select TIME_FORMAT(now(),'%T')")) !== false) 
  {
    if (($row1 = $db->fetchrow($rez1)) !== false)
    {
      mjlog(DEBUG,SUMFNAME,"dbTime[".$row1[0]."]",__LINE__);
      $smarty->assign('dbTime',$row1[0]);
    }
    else mjlog(DEBUG,SUMFNAME,"failed to get row",__LINE__);
  }
  else mjlog(DEBUG,SUMFNAME,"failed to get queryu",__LINE__);
  $smarty->assign('serverTime', date('H:i:s'));

  $smarty->assign('username', mj_UserBase::getUserFullName($userId));
  $smarty->assign('summaryList', $summaryList);
  $smarty->assign('totalDuration', substr($row[0],0,8));
  $smarty->assign('thedate', $datedstr);
  $smarty->assign('userStatus', isset(mj_UserBase::$currUser->data['status']) ? mj_UserBase::$currUser->data['status'] : "");
  $smarty->assign('onLogin',"");
}

////////////////////////////////////////////////////////////////////////////////
/// You won't need these functins but they have to exist.
////////////////////////////////////////////////////////////////////////////////

function summary_top($db,&$smarty) 
{ 
  global $page,$cmd,$subcmd,$errmsg;

  // Code here goes into the javascript block at the top of the data entry page
}

function summary_bottom($db,&$smarty) 
{ 
  global $page,$cmd,$subcmd,$errmsg;

  // Code here goes into the javascript block at the top of the data entry page
}
