
//  /my_application.php?path=UserMaint.UserMaintJson&cmd=getUserData&uid=5
//  /my_application.php?path=UserMaint.UserMaintJson&cmd=getUserData&subcmd=getGroupsAndRights&uid=10

function setUserRightsList(rights)
{
    // set values into the list of set rightw for the user
    mj_clearOptionList("rightsList");
    if (rights.length > 0)
    {
      for (var ii=0; ii < rights.length; ii++)
      {
        mj_appendOptionLast("rightsList", rights[ii], rights[ii]);
      }
    }
    else
      mj_appendOptionLast("rightsList", "No Assigned Rights", "");
}

function showEdit(showit)
{
  mjDisplay('userGroupsDiv',showit);
  mjDisplay('rightsDetailTd',showit);
  mjDisplay('userDataTbl',showit);
}

////////////////////////////////////////////////////////////////////////////////

function fetchUserRecordCallback(json)
{
  writeConsole("in fetchBinDataCallback");
  if (json.errmsg.trim() != "")
    mj_alert("ERROR",json.errmsg);
  else
  {
    var data = json.data;
    writeConsole("fetchBinDataCallback: id["+data.id+"] fullName["+data.fullName+"]");

    var list = data;
    for (key in list)
    {
      var value = list[key];
      writeConsole("UserMaintHtml.js::fetchUserRecordCallback: key["+key+"] value["+value+"]");
    }

    var frm = document.forms.userEditForm;
    mjset("uidLabel",data["id"]);
    frm.uid.value          = data["id"];
    frm.userFullName.value = data["fullName"];
    if (MJ_VALIDATE_FIELD && (MJ_VALIDATE_FIELD != "") && (MJ_VALIDATE_FIELD != "email"))
    {
      //frm.userHandle.value   = data["handle"];
      mjset(MJ_VALIDATE_FIELD_NAME,data[MJ_VALIDATE_FIELD]);
    }
    frm.userEmail.value    = data["email"];
    frm.userPassword.value = "";
    mjselect(frm.userStatus,data["status"]);

    // set values into the list of set rightw for the user
    mj_clearOptionList("rightsList");
    if (data["rights"].length > 0)
    {
      setUserRightsList(data["rights"]);
    }

    // set the groups available 
    mj_clearOptionList("availableGroupsSelect");
    for (var ii=0; ii < data["allGroups"].length; ii++)
    {
      mj_appendOptionLast("availableGroupsSelect", data["allGroups"][ii]["name"], data["allGroups"][ii]["id"]);
    }

    // set the groups the user actually belongs to.
    mj_clearOptionList("userMemberGroupsSelect");
    for (var ii=0; ii < data["groups"].length; ii++)
    {
      mj_appendOptionLast("userMemberGroupsSelect", data["groups"][ii]["name"], data["groups"][ii]["id"]);
      mj_removeOptionByValue("availableGroupsSelect",data["groups"][ii]["id"]);
    }
  }
}

function fetchUserRecord(uid)
{
  if (uid != "")
  {
    var url = 'my_application.php?path=UserMaint.UserMaintJson&cmd=getUserData&subcmd=getGroupsAndRights&uid='+uid;
    writeConsole(url);
    $.getJSON(url, function(data){ fetchUserRecordCallback(data); });
  }
}

////////////////////////////////////////////////////////////////////////////////

function fetchAllGroupsCallback(json)
{
  writeConsole("in fetchBinDataCallback");
  if (json.errmsg.trim() != "")
    mj_alert("ERROR",json.errmsg);
  else
  {
    var data = json.data;
    // set the groups available 
    mj_clearOptionList("availableGroupsSelect");
    for (var ii=0; ii < data["allGroups"].length; ii++)
    {
      mj_appendOptionLast("availableGroupsSelect", data["allGroups"][ii]["name"], data["allGroups"][ii]["id"]);
    }
  }
}

function fetchAllGroups()
{
  var url = 'my_application.php?path=UserMaint.UserMaintJson&cmd=getAvailableGroups';
  writeConsole(url);
  $.getJSON(url, function(data){ fetchAllGroupsCallback(data); });
}

////////////////////////////////////////////////////////////////////////////////

function showUserRecord()
{
  writeConsole("showUserRecord()");
  var slist = document.forms.userSelectForm.userSelectList;
  var idx = slist.selectedIndex;

  var uid = slist.options[idx].value;

  writeConsole("showUserRecord() uid["+(uid?uid:"not found")+"]");
  
  fetchUserRecord(uid);
  showEdit(true);
}

////////////////////////////////////////////////////////////////////////////////

function editNewUser()
{
  writeConsole("editNewUser()");
  var frm = document.forms.userEditForm;
  mjset("uidLabel","");
  frm.uid.value = "";
  frm.userFullName.value = "";
  mjset(MJ_VALIDATE_FIELD_NAME,""); //frm.userHandle.value = "";
  frm.userEmail.value = "";
  frm.userPassword.value = "";
  mjselect(frm.userStatus,"active");
  mj_clearOptionList("userMemberGroupsSelect");
  mj_clearOptionList("availableGroupsSelect");
  fetchAllGroups();
  showEdit(true);
  //top.document.location = "my_application.php?path=UserMaint.UserMaintHtml";
}

////////////////////////////////////////////////////////////////////////////////

function updateRightsListingCallback(json)
{
  writeConsole("updateRightsListingCallback()");
  data = json["data"];
  // set values into the list of set rights for the user
  mj_clearOptionList("rightsList");
  if (data["rights"].length > 0)
  {
    setUserRightsList(data["rights"]);
  }
}

////////////////////////////////////////////////////////////////////////////////

function updateRightsListing()
{
  writeConsole("updateRightsListing()");
  var slist = document.forms.userEditForm.userMemberGroupsSelect;
  var url = 'my_application.php?path=UserMaint.UserMaintJson&cmd=getUserTmpRights&groups='+ cosolidateMemberGroupIds();
  writeConsole("updateRightsListing: "+url);
  $.getJSON(url, function(data){ updateRightsListingCallback(data); });
}

////////////////////////////////////////////////////////////////////////////////

function removeGroupFromUser()
{
  writeConsole("removeGroupFromUser()");
  var slist = document.forms.userEditForm.userMemberGroupsSelect;
  var idx = slist.selectedIndex;

  var gid   = slist.options[idx].value;
  var gname = slist.options[idx].text;

  mj_appendOptionLast("availableGroupsSelect", gname, gid)
  mj_removeOptionSelected("userMemberGroupsSelect");
  updateRightsListing();
}

////////////////////////////////////////////////////////////////////////////////

function addGroupToUser()
{
  writeConsole("addGroupToUser()");
  var slist = document.forms.userEditForm.availableGroupsSelect;
  var idx = slist.selectedIndex;

  var gid   = slist.options[idx].value;
  var gname = slist.options[idx].text;

  mj_appendOptionLast("userMemberGroupsSelect", gname, gid)
  mj_removeOptionSelected("availableGroupsSelect");
  updateRightsListing();
}

////////////////////////////////////////////////////////////////////////////////

/*
function saveUserEditCallback(status,msg)
{
  writeConsole("in fetchBinDataCallback");
  mj_alert(status,msg);
}
*/

////////////////////////////////////////////////////////////////////////////////

function cosolidateMemberGroupIds()
{
  var sel = document.forms.userEditForm.userMemberGroupsSelect;
  writeConsole("cosolidateMemberGroupIds: lenth["+sel.length+"]");

  var rez = "";
  var comma = "";
  for (var ii=0; ii < sel.length; ii++)
  {
    writeConsole("cosolidateMemberGroupIds: adding["+sel.options[ii].value+"]");
    rez += comma + sel.options[ii].value;
    comma = ",";
  }
  writeConsole("cosolidateMemberGroupIds: rez["+rez+"]");
  return rez;
}

////////////////////////////////////////////////////////////////////////////////

function saveUserEdit()
{
  //writeConsole("saveUserEdit()");

  document.forms.userEditForm.userMemberGroups.value = cosolidateMemberGroupIds();
  return true;
}

////////////////////////////////////////////////////////////////////////////////

function cancelUserEdit()
{
  document.location = "/my_application.php?path=UserMaint.UserMaintHtml";
}

function reloadUserEditPage(errmsg)
{
  document.location = "/my_application.php?path=UserMaint.UserMaintHtml&errmsg="+encodeURI(errmsg);
}










