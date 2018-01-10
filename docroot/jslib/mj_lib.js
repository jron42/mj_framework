/********************************************************************************
 * Copywrite © 2010,2011,2012,2013,2014 John Morgan
 * This file provided from the personal library of John Morgan and ownership of 
 * this code is retained as such.
 * The right to free use is provided to ZAI Inc..
 ********************************************************************************/

String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); }

$.ajaxSetup({"error":function(XMLHttpRequest,textStatus, errorThrown) {
      alert("textStatus: "+textStatus+"\n\nerrorThrown: "+errorThrown+"\n\nresponseText: "+XMLHttpRequest.responseText);
      //alert(errorThrown);
      //alert(XMLHttpRequest.responseText);
}});

function getInternetExplorerVersion()
{
  // Returns the version of Internet Explorer or a -1 (indicating the use of another browser).
  var rv = -1; // Return value assumes failure.
  if (navigator.appName == 'Microsoft Internet Explorer')
  {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
      rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

////////////////////////////////////////////////////////////////////////////////

var version = "1.0";

function writeConsole(msg)
{
  if (0) return;

  var is_chrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
  if (is_chrome) console.debug(msg);
  else if (getInternetExplorerVersion() == -1)
    console.log(msg);
}

writeConsole("mj_lib.js loaded: "+version);

////////////////////////////////////////////////////////////////////////////////

function mj_isdefined(variable)
{
  return (typeof(window[variable]) == "undefined") ? false : true;
}

function mjset(name,value)
{
  writeConsole("setting name["+name+"] value["+value+"]");
  var foo = document.getElementById(name);
  if (!foo)              return false;
  if (foo === null)      return false;
  if (foo === undefined) return false;
  if (value === null)    value = "";

  //alert ("foo.toString = "+ foo.toString());
  //alert ("foo.type = "+ foo.type);
  if (foo.toString() == "[object HTMLInputElement]" && foo.type == "text")
  {
    foo.value = value;
  }
  else if (foo.toString() == "[object HTMLTextAreaElement]" && foo.type == "textarea")
  {
    foo.value = value;
  }
  else foo.innerHTML = value;
  return true;
}

function mjget(name)
{
  var foo = document.getElementById(name);
  if (!foo) return "";

  if      (foo.toString() == "[object HTMLInputElement]"    && foo.type == "text")     return foo.value;
  else if (foo.toString() == "[object HTMLTextAreaElement]" && foo.type == "textarea") return foo.value;

  return foo.innerHTML;
}

function mjcheck(checkgroup,value)
{
  for (ii=0; ii<checkgroup.length; ii++){
    if (checkgroup[ii].value == value)
      checkgroup[ii].checked = true;
  }
}

/**
 * Show or hide a screen element
 **/
function mjDisplay(theid,showit)
{
  writeConsole("mjDisplay: name["+theid+"] show["+showit+"]"); 
  var foo = document.getElementById(theid);
  if (foo)
  {
    if (showit) foo.style.display = 'block';
    else        foo.style.display = 'none';
  }
  else writeConsole("mjDisplay: name["+theid+"] NOT FOUND");
}


/**
 * get the value of the first checked item in a check group
 **/
function mjgetchecked(checkgroup)
{
  for (ii=0; ii<checkgroup.length; ii++){
    if (checkgroup[ii].checked == true)
      return checkgroup[ii].value;
  }
  return -1;
}

function mj_getchecked(checkboxId)
{
  var foo = document.getElementById(checkboxId);
  return foo.checked;
}

////////////////////////////////////////////////////////////////////////////////
// select list management
//

/**
 * selection the given option based on matching the given value
 */
function mjselect(ddl,value)
{
  //writeConsole("mjselect: select value["+value+"]");
  for (var ii = 0; ii < ddl.options.length; ii++) {
      if (ddl.options[ii].value == value) {
          if (ddl.selectedIndex != ii) {
              ddl.selectedIndex = ii;
          }
          break;
      }
  }
}
function mjselectOptionById(selid,value)
{
  var sel = document.getElementById(selid);
  mjselect(sel,value);
}

function mjgetSelectedValue(selid)
{
  var sel = document.getElementById(selid);
  if (sel.selectedIndex >= 0)
    return sel.options[sel.selectedIndex].value;
  return '';
}

/**
 * this function is screwed up. Do not use.
 */
function mj_setSimpleOptions(selid,thelist,selection)
{
  writeConsole("mj_setSimpleOptions: selid["+selid+"] selection["+selection+"]");
  var sel = document.getElementById(selid);

  sel.length = 0;
  sel.add(new Option("All Categories", "0"));

  for (var ii = 0; ii < thelist.length; ii++)
  {
    key = thelist[ii];
    writeConsole("    key["+key+"]");
    sel.add(new Option(key, key));
  }
  if (undefined != selection) mjselect(sel,selection);
}

////////////////////////////////////////////////////////////////////////////////
// select list management - stolen from da interwebs..
//
function mj_insertOptionBeforeSelected(olistId, text, value)
{
  var elSel = document.getElementById(olistId);
  if (elSel.selectedIndex >= 0) 
  {
    var elOptNew   = document.createElement('option');
    elOptNew.text  = text;
    elOptNew.value = value;
    var elOptOld   = elSel.options[elSel.selectedIndex]; 
    try {
      elSel.add(elOptNew, elOptOld); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      elSel.add(elOptNew, elSel.selectedIndex); // IE only
    }
  }
}
 
function mj_insertOptionAtIndex(olistId, idx, text, value)
{
  writeConsole("mj_insertOptionAtIndex: olistId["+olistId+"] idx["+idx+"] text["+text+"] value["+value+"]");
  if (value == "") value = text;

  var elSel = document.getElementById(olistId);
  if (elSel.length < idx) 
    return mj_appendOptionLast(olistId, text, value);

  if (elSel.selectedIndex >= 0) 
  {
    var elOptNew   = document.createElement('option');
    elOptNew.text  = text;
    elOptNew.value = value;
    var elOptOld   = elSel.options[elSel.selectedIndex]; 
    try {
      elSel.add(elOptNew, elOptOld); // standards compliant; doesn't work in IE
    }
    catch(ex) {
      elSel.add(elOptNew, elSel.selectedIndex); // IE only
    }
  }
}
 
function mj_appendOptionLastWobj(olistObj, text, value)
{
  writeConsole("mj_appendOptionLastWobj: text["+text+"] value["+value+"]");
  var elOptNew   = document.createElement('option');
  elOptNew.text  = text;
  elOptNew.value = value;
   
  try {
    olistObj.add(elOptNew, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    olistObj.add(elOptNew); // IE only
  }
}
function mj_appendOptionLast(olistId, text, value)
{
  writeConsole("mj_appendOptionLast: olistId["+olistId+"] text["+text+"] value["+value+"]");
  var elSel = document.getElementById(olistId);
  mj_appendOptionLast(elSel, text, value);
}

function mj_removeAllOptions(olistId)
{
  writeConsole("mj_removeOption: olistId["+olistId+"]");
  var elSel = document.getElementById(olistId);
  elSel.length = 0;
}
function mj_clearOptionList(olistId) { mj_removeAllOptions(olistId); }  // an alias

function mj_removeOption(olistId,idx)
{
  writeConsole("mj_removeOption: olistId["+olistId+"] idx["+idx+"]");
  var elSel = document.getElementById(olistId);
  if (idx > elSel.length)
    return;
  elSel.remove(idx);
}

function mj_removeOptionByValue(olistId,value)
{
  writeConsole("mj_removeOptionByValue: olistId["+olistId+"]");
  var elSel = document.getElementById(olistId);
  for (var ii = elSel.length - 1; ii >= 0; ii--) 
  {
    if (elSel.options[ii].value == value) 
    {
      writeConsole("mj_removeOptionByValue: removing["+ii+"]");
      elSel.remove(ii);
    }
  }
}

function mj_removeOptionSelected(olistId)
{
  writeConsole("mj_removeOptionSelected: olistId["+olistId+"]");
  var elSel = document.getElementById(olistId);
  var ii;
  for (ii = elSel.length - 1; ii >= 0; ii--) 
  {
    if (elSel.options[ii].selected) 
    {
      writeConsole("mj_removeOptionSelected: removing["+ii+"]");
      elSel.remove(ii);
    }
  }
}

function mj_removeOptionLast(olistId)
{
  var elSel = document.getElementById(olistId);
  if (elSel.length > 0)
  {
    elSel.remove(elSel.length - 1);
  }
}

////////////////////////////////////////////////////////////////////////////////

function mj_noEnter()
{
  return !(window.event && window.event.keyCode == 13);
}

function mj_openSelect(element)
{
  if (window.event && window.event.keyCode == 13)
  {
    if (document.createEvent) // all browsers
    {
      var e = document.createEvent("MouseEvents");
      e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
      worked = element.dispatchEvent(e);
    }
    else if (element.fireEvent) // ie
    {
      worked = element.fireEvent("onmousedown");
    }
  }
  return false;
}

function mj_onEnterCallFunc(func)
{
  if (window.event && window.event.keyCode == 13)
  {
    func();
    return false;
  }
  return true;
}

////////////////////////////////////////////////////////////////////////////////

function mj_deleteCookie(name)
{
  mj_setCookie(name,0,-1);
}

function mj_setCookie(cname,value,expdays)
{
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + expdays);
  var cval = escape(value) + ((expdays==null) ? " " : "; expires="+exdate.toUTCString());
  document.cookie = cname + "=" + cval;
}

function mj_getCookie(cname)
{
  var cookieArray = document.cookie.split(";");
  for (var ii=0; ii<cookieArray.length; ii++)
  {
    /*
    var eqidx = cookieArray[ii].indexOf("=");
    var name  = cookieArray[ii].substr(0,eqidx);
    var value = cookieArray[ii].substr(eqidx+1);
    name = name.replace(/^\s+|\s+$/g,"");
    */
    writeConsole("mj_getCookie: cookieArray[ii]=["+cookieArray[ii]+"] <br>\b");
    var parts = cookieArray[ii].split("=");
    var name = parts[0].trim();
    //writeConsole("mj_getCookie: checking["+name+"] == ["+cname+"] - nparts["+parts.length+"]<br>\b");
    if (name == cname)
    {
      if (!parts[1] || parts[1] == 'undefined')
      {
        //writeConsole("mj_getCookie: ---- found["+name+"] returning[''] <br>\b");
        return "";
      }
      var value = unescape(parts[1]);
      writeConsole("mj_getCookie: ---- found["+name+"] returning["+value+"]<br>\b");
      return value;
    }
  }
  return "";
}

function mj_getCookieData(name)
{
  var json = mj_getCookie(name);
  if (json == "undefined")
    writeConsole("mj_getCookieData: json == 'undefined'  <br>\b");
  if (json)
  {
    writeConsole("mj_getCookieData: got a cookie from mj_getCookie - cartjson= "+json+"<br>\b");
    return jQuery.parseJSON(json);
  }
  writeConsole("mj_getCookieData: nothing returned from mj_getCookie - returning empty array<br>\b");
  return new Array();
}

function mj_setCookieData(name, cartArray, expire)
{
  writeConsole("cart_setCookie: name["+name+"] cartArray.length["+cartArray.length+"]<br>\b");
  var jstr = JSON.stringify(cartArray);
  writeConsole("mj_setCookieData name["+name+"] exp["+expire+"] json["+jstr+"]");
  mj_setCookie(name,jstr,expire);
}

////////////////////////////////////////////////////////////////////////////////

function mj_reformatPhone(fld,err_id)
{
  //alert(fld.value);
  var str = fld.value.trim();
  var str2 = "";

  if (str.length == 0) return; // not checking for required fields, just reformatting

  // get a naked string of numbers
  for (var ii=0; ii < str.length; ii++)
  {
    var val = parseInt(str.charAt(ii));
    if (str.charAt(ii) == "0" || (val != NaN && val > 0 && val < 10))
    {
      str2 += str.charAt(ii);
    }
  }
  //alert(str2);
  if (str2.length < 10) 
  {
    if (err_id && err_id != "") mjDisplay(err_id,true);
    return;
  }
  if (str2.length == 11 && str2.charAt(11) == "1")
    str2 = str2.substr(1);
  var rez = str2.substr(0,3) + "-" + str2.substr(3,3) + "-" + str2.substr(6);
  fld.value = rez;
}

////////////////////////////////////////////////////////////////////////////////

/*
function addTableRow(jQtable){
    jQtable.each(function(){
        var $table = $(this);
        // Number of td's in the last table row
        var n = $('tr:last td', this).length;
        var tds = '<tr>';
        for(var i = 0; i < n; i++){
            tds += '<td>&nbsp;</td>';
        }
        tds += '</tr>';
        if($('tbody', this).length > 0){
            $('tbody', this).append(tds);
        }else {
            $(this).append(tds);
        }
    });
}

function addTableRow(jQtable,row){
    jQtable.each(function(){
        var $table = $(this);
        var tds = row;
        //alert("tbody.len:"+$('tbody', this).length);
        if ($('tbody', this).length > 0){
            $('tbody', this).append(tds);
        } else {
            $(this).append(tds);
        }
    });
}
*/
function addTableRow(table,row)
{
  //alert("tbody.len:"+$('tbody', table).length);
  //alert("addTableRow: "+row);
  if ($('tbody', table).length > 0){
    $('tbody', table).append(row);
  } else {
    $(table).append(row);
  }
}

function deleteTableRows(tablename,rowstoleave) // rownum == number of leading rows to leave (for headers)
{
  var table = document.getElementById(tablename);
  //var rowCount = $('tbody', table).length;
  var rowCount = table.rows.length;;
  //alert("deleteTableRows rowstoleave["+rowstoleave+"] rowCount["+rowCount+"]");
  for (var ii=rowstoleave; ii<rowCount; ii++) 
  {
    table.deleteRow(ii);
    rowCount--;
    ii--;
  }
}

function deleteTableRow(tablename,rownum) // rownum == number of leading rows to leave (for headers)
{
  //var rowCount = $('tbody', table).length;
  var table = document.getElementById(tablename);
  var rowCount = table.rows.length;
  //alert("deleteTableRow: calling table.deleteRow("+rownum+")");
  table.deleteRow(rownum);
}

////////////////////////////////////////////////////////////////////////////////

// Simulates PHP's date function
Date.prototype.format = function(format) {
	var returnStr = '';
	var replace = Date.replaceChars;
	for (var i = 0; i < format.length; i++) {
		var curChar = format.charAt(i);
		if (i - 1 >= 0 && format.charAt(i - 1) == "\\") { 
			returnStr += curChar;
		}
		else if (replace[curChar]) {
			returnStr += replace[curChar].call(this);
		} else if (curChar != "\\"){
			returnStr += curChar;
		}
	}
	return returnStr;
};
 
Date.replaceChars = {
	shortMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
	longMonths: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
	shortDays: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
	longDays: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
	
	// Day
	d: function() { return (this.getDate() < 10 ? '0' : '') + this.getDate(); },
	D: function() { return Date.replaceChars.shortDays[this.getDay()]; },
	j: function() { return this.getDate(); },
	l: function() { return Date.replaceChars.longDays[this.getDay()]; },
	N: function() { return this.getDay() + 1; },
	S: function() { return (this.getDate() % 10 == 1 && this.getDate() != 11 ? 'st' : (this.getDate() % 10 == 2 && this.getDate() != 12 ? 'nd' : (this.getDate() % 10 == 3 && this.getDate() != 13 ? 'rd' : 'th'))); },
	w: function() { return this.getDay(); },
	z: function() { var d = new Date(this.getFullYear(),0,1); return Math.ceil((this - d) / 86400000); }, // Fixed now
	// Week
	W: function() { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((((this - d) / 86400000) + d.getDay() + 1) / 7); }, // Fixed now
	// Month
	F: function() { return Date.replaceChars.longMonths[this.getMonth()]; },
	m: function() { return (this.getMonth() < 9 ? '0' : '') + (this.getMonth() + 1); },
	M: function() { return Date.replaceChars.shortMonths[this.getMonth()]; },
	n: function() { return this.getMonth() + 1; },
	t: function() { var d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 0).getDate() }, // Fixed now, gets #days of date
	// Year
	L: function() { var year = this.getFullYear(); return (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0)); },	// Fixed now
	o: function() { var d  = new Date(this.valueOf());  d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3); return d.getFullYear();}, //Fixed now
	Y: function() { return this.getFullYear(); },
	y: function() { return ('' + this.getFullYear()).substr(2); },
	// Time
	a: function() { return this.getHours() < 12 ? 'am' : 'pm'; },
	A: function() { return this.getHours() < 12 ? 'AM' : 'PM'; },
	B: function() { return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24); }, // Fixed now
	g: function() { return this.getHours() % 12 || 12; },
	G: function() { return this.getHours(); },
	h: function() { return ((this.getHours() % 12 || 12) < 10 ? '0' : '') + (this.getHours() % 12 || 12); },
	H: function() { return (this.getHours() < 10 ? '0' : '') + this.getHours(); },
	i: function() { return (this.getMinutes() < 10 ? '0' : '') + this.getMinutes(); },
	s: function() { return (this.getSeconds() < 10 ? '0' : '') + this.getSeconds(); },
	u: function() { var m = this.getMilliseconds(); return (m < 10 ? '00' : (m < 100 ?
'0' : '')) + m; },
	// Timezone
	e: function() { return "Not Yet Supported"; },
	I: function() { return "Not Yet Supported"; },
	O: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + '00'; },
	P: function() { return (-this.getTimezoneOffset() < 0 ? '-' : '+') + (Math.abs(this.getTimezoneOffset() / 60) < 10 ? '0' : '') + (Math.abs(this.getTimezoneOffset() / 60)) + ':00'; }, // Fixed now
	T: function() { var m = this.getMonth(); this.setMonth(0); var result = this.toTimeString().replace(/^.+ \(?([^\)]+)\)?$/, '$1'); this.setMonth(m); return result;},
	Z: function() { return -this.getTimezoneOffset() * 60; },
	// Full Date/Time
	c: function() { return this.format("Y-m-d\\TH:i:sP"); }, // Fixed now
	r: function() { return this.toString(); },
	U: function() { return this.getTime() / 1000; }
};


////////////////////////////////////////////////////////////////////////////////

/**
 * DHTML date validation script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */
// Declaring valid date character, minimum year and maximum year

var dtCh= "/";
var minYear=1900;
var maxYear=2100;

function isInteger(s)
{
    var i;
    for (i = 0; i < s.length; i++){   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}

function stripCharsInBag(s, bag)
{
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++){   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year)
{
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}
function DaysArray(n) {
	for (var i = 1; i <= n; i++) {
		this[i] = 31
		if (i==4 || i==6 || i==9 || i==11) {this[i] = 30}
		if (i==2) {this[i] = 29}
   } 
   return this
}

function mjIsDate(dtStr,fieldname)
{
	var daysInMonth = DaysArray(12)
	var pos1=dtStr.indexOf(dtCh)
	var pos2=dtStr.indexOf(dtCh,pos1+1)
	var strMonth=dtStr.substring(0,pos1)
	var strDay=dtStr.substring(pos1+1,pos2)
	var strYear=dtStr.substring(pos2+1)
	strYr=strYear
	if (strDay.charAt(0)=="0" && strDay.length>1) strDay=strDay.substring(1)
	if (strMonth.charAt(0)=="0" && strMonth.length>1) strMonth=strMonth.substring(1)
	for (var i = 1; i <= 3; i++) {
		if (strYr.charAt(0)=="0" && strYr.length>1) strYr=strYr.substring(1)
	}
	month=parseInt(strMonth)
	day=parseInt(strDay)
	year=parseInt(strYr)
	if (pos1==-1 || pos2==-1){
		alert(fieldname+": The date format should be : mm/dd/yyyy")
		return false
	}
	if (strMonth.length<1 || month<1 || month>12){
		alert(fieldname+": Please enter a valid month")
		return false
	}
	if (strDay.length<1 || day<1 || day>31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month]){
		alert(fieldname+": Please enter a valid day")
		return false
	}
	if (strYear.length != 4 || year==0 || year<minYear || year>maxYear){
		alert(fieldname+": Please enter a valid 4 digit year between "+minYear+" and "+maxYear)
		return false
	}
	if (dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false){
		alert(fieldname+": Please enter a valid date")
		return false
	}
  return true
}

////////////////////////////////////////////////////////////////////////////////

var MJ_CRUMB_COOKIE_NAME     = "CRUMBS";
var MJ_CRUMB_COOKIE_EXP_DAYS = 1;

function mj_drawCrumb(spotId, seper)
{
  var elem = document.getElementById(spotId);
  if (!elem)
  {
    writeConsole("mj_drawCrumb: elem not found: spotId["+spotId+"]");
    return;
  }
  var sep = "&nbsp;&gt;&gt;&nbsp;";
  if (seper) sep = seper;

  var crumblist = mj_getCookieData(MJ_CRUMB_COOKIE_NAME);
  var str = "";

  writeConsole("mj_drawCrumb: crumblist.length["+crumblist.length+"]");
  var ii = 0;
  if (crumblist.length > 0)
  {
    if (crumblist.length > 1)
    {
      for (ii=0; ii < crumblist.length-1; ii++)
      {
        str += sep + "<a href=\""+crumblist[ii]["url"]+"\">"+crumblist[ii]["name"]+"</a> ";
      }
    }
    str += sep + crumblist[ii]["name"];
  } 
  mjset(spotId,str);
}

function mj_pushCrumb(name,url,data)
{
  writeConsole("mj_pushCrumb: name["+name+"] url["+url+"]");

  if (name == "" || url == "") return;

  var crumblist    = mj_getCookieData(MJ_CRUMB_COOKIE_NAME);
  var newcrumblist = new Array();

  var crumb = { };
  crumb["name"] = name;
  crumb["url"]  = url;
  crumb["data"] = data;

  // make sure we don't make a loop
  // easiest is to just build a new array and stop if we find the current page already in the path
  //
  if (crumblist.length > 0)
  {
    for (var ii=0; ii < crumblist.length; ii++)
    {
      if (crumblist[ii]["name"] == name) // we found an entry of the same name so replace and return
      {
        writeConsole("mj_pushCrumb: found: name["+name+"] setting and returning");
        newcrumblist.push(crumb);
        mj_setCookieData(MJ_CRUMB_COOKIE_NAME,newcrumblist,MJ_CRUMB_COOKIE_EXP_DAYS);
        return;
      }
      newcrumblist.push(crumblist[ii]); // throw the current one on the stack
    }
  }
  newcrumblist.push(crumb); // add the new one

  mj_setCookieData(MJ_CRUMB_COOKIE_NAME,newcrumblist,MJ_CRUMB_COOKIE_EXP_DAYS);
}

function mj_getCrumbData(name)
{
  var crumblist = mj_getCookieData(MJ_CRUMB_COOKIE_NAME);

  for (var ii=0; ii < crumblist.length-1; ii++)
  {
    if (crumblist[ii]["name"] == name) return crumblist[ii];
  }
  return null;
}

function mj_popCrumb()
{
  var crumblist = mj_getCookieData(MJ_CRUMB_COOKIE_NAME);
  if (crumblist.length > 0)
  {
    crumblist.pop();
    mj_setCookieData(MJ_CRUMB_COOKIE_NAME,crumblist,MJ_CRUMB_COOKIE_EXP_DAYS);
  }
}

////////////////////////////////////////////////////////////////////////////////

function mj_closePopup()
{
  $('msg_popup').bPopup().close();
}

function mj_alert(title,msg)
{
  mjset('msg_popup_title',title);
  mjset('msg_popup_msg',msg);
  $('#msg_popup').bPopup({ modalClose:true });
}













