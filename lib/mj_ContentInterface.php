<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to the ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_ContentInterface.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

interface mj_ContentInterface
{
  function execute($cmd,$subcmd,$errmsg,$currUserObj); 

  function headerScript();
  function footerScript();
  function dump();
}

