<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************
 * $Revision: 1.2 $
 * $Id: mj_ResponseJson.php,v 1.2 2016/08/05 03:02:19 jmorgan Exp $
 ********************************************************************************/

class mj_ResponseRaw extends mj_ResponseInterface 
{
  protected $data = '';

  protected $path         = "";
  protected $cmd          = "";
  protected $subcmd       = "";
  protected $errmsg       = "";
  protected $mimeType     = "";

  function __construct($moduleName)
  {
    parent::__construct($moduleName);
  }

  function execute($path,$cmd,$subcmd,$errmsg)
  {
    $this->path   = $path;
    $this->cmd    = $cmd;
    $this->subcmd = $subcmd;
    $this->errmsg = $errmsg;

    //parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass
    //
    // do your stuff
    //
  }

  // override this method with your requirements
  //function createContent();

  function outputResponseHeader() // $type='javascript')
  {
    header('Content-Type: application/json');
  }

  function dump($errmsg)
  {
    $this->outputResponseHeader();
    echo $this->data;
  }
}

