<?php
/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

mj_require_once("mj_ResponseHtmlPage.php");
mj_require_once("UserMaint.php");

class UserMaintHtml extends mj_ResponseHtmlPage
{
  function __construct($moduleName) 
  {
    parent::__construct($moduleName);

    $this->headerJsFiles[] = "modules/UserMaint/js/UserMaintHtml.js";

    $this->headerScript = ""; //"<script>alert('made it to some include script in the UserMaintHtml class');</script>\n";
  }

  static public function leftMenu($tplname,$slotname)
  {
    return "<br>UserMaintHtml.leftMenu: tplname[$tplname] slotname[$slotname]'<br>\n";
  }

  function execute($path,$cmd,$subcmd,$errmsg)
  {
    parent::execute($path,$cmd,$subcmd,$errmsg); // you will want to call this in your subclass

    $LINE         = "";
    $tpl          = "";
    $content_html = "";

    $smarty = &$this->smarty;
 
    try
    {
      $this->setBaseTemplateValues($smarty);

      switch ($cmd)
      {
        case 'changePass':
          if (!mj_UserBase::$currUser->isAllowed('changePass'))
            { $LINE = __LINE__; throw new Exception("NOTICE: Invalid rights for this operation (change password)."); }
          $tpl = "password.tpl.php";
	  break;

        default:
          if (!mj_UserBase::$currUser->isAllowed('editUser'))
            { $LINE = __LINE__; throw new Exception("NOTICE: Invalid rights for this operation (Edit Users)."); }

          $smarty->assign('MJ_USER_FULLNAME',         MJ_USER_FULLNAME ? "1" : "0");
          $smarty->assign('MJ_VALIDATE_FIELD',        MJ_VALIDATE_FIELD);
          $smarty->assign('MJ_VALIDATE_FIELD_NAME',   MJ_VALIDATE_FIELD_NAME);
          $smarty->assign('MJ_VALIDATE_FIELD_PROMPT', MJ_VALIDATE_FIELD_PROMPT);
          $smarty->assign('userList',                 $this->getAllUsers());
          $smarty->assign('availableGroups',          mj_RightsGroups::getGroupsAsOptions_s());
          $tpl = "UserMaintHtml.tpl.php";

          $this->headerScript = "<script>\n"
                              . '  var MJ_USER_FULLNAME         = '.  (MJ_USER_FULLNAME ? "1" : "0") .";\n"
                              . '  var MJ_VALIDATE_FIELD        = "'.  MJ_VALIDATE_FIELD             ."\";\n"
                              . '  var MJ_VALIDATE_FIELD_NAME   = "'.  MJ_VALIDATE_FIELD_NAME        ."\";\n"
                              . '  var MJ_VALIDATE_FIELD_PROMPT = "'.  MJ_VALIDATE_FIELD_PROMPT      ."\";\n"
                              . "</script>\n";
	  break;
      }

      mjlog(DEBUG,__CLASS__.':'.__FUNCTION__,"fetching template [$tpl]",__LINE__);
      $this->htmlcode = $smarty->fetch($tpl);

      $smarty->clear_all_assign();
    }
    catch (Exception $e)
    {
      mjlog(ERROR,__CLASS__.':'.__FUNCTION__,"Exception caught: ".$e->getMessage(),$LINE);
      $this->data['data'] = "";
      $this->htmlcode = "<br>".$e->getMessage();;
    }
  } 

  private function getAllUsers()
  {
    //$rez = mj_User::$userDb->fetchRowsAsAssocArray("select id,fullName,status from Users order by fullName");
    $rez = mj_User::$userDb->fetchAsOptions("select id,fullName,status from Users order by fullName");
    return $rez;
  }

}























