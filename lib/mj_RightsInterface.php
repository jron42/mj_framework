<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_RightsInterface.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

interface mj_RightsUserDbInterface
{
  public function __construct($userId);

  public static function allowed(  $userId, $privName);
  public        function isAllowed($privName);

  public function addGroup(   $privName);
  public function removeGroup($privName);
  public function getGroups();
  public function getAllPrivs();
}

interface mj_RightsGroupDbInterface
{
  public function __construct($groupName);

  public static function addGroup(   $userId, $privName);
  public static function removeGroup($userId, $privName);
  public static function getGroups( );

  public function addPriv(   $privName);
  public function removePriv($privName);
  public function getPrivs();
}

