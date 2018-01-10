

//alert('UserMaint.js');

function validateChangePassword()
{
  // function to validate the form for submittal.
  //alert("in validateChangePassword()");

  var frm = document.forms.myform;

  if (frm.pass0.value.trim().length < 6)
  {
    mj_alert("Notice:","Old Password too short.");
    return false;
  }
  if (frm.pass1.value.trim().length < 6)
  {
    mj_alert("","Password too short.");
    return false;
  }
  if (frm.pass1.value.trim() != frm.pass2.value.trim())
  {
    mj_alert("","New passwords do not match.");
    return false;
  }
  return true;
}



