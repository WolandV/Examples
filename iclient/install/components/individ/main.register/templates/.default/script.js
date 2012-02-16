function SetCompanyFromSearch(idCompany)
{
    sc = $('#search_company').val();
    if(sc == placeholder)
        sc = '';
    $.post("/bitrix/templates/individ/ajax/ajax_company_set.php", {'SEARCH_COMPANY':sc,'UF_COMPANY':idCompany,'ajax':'y'}, function(data){
        if(data.length > 1)    
        {
            $('#OurCompany').html(data);
            $('#OurCompany').show();
            $('#PH').hide(); 
        }
        else
        {
            $('#OurCompany').html('');
            $('#OurCompany').hide();
            $('#PH').show();
        }
    });
    return false;
}

function GetFormCompany(WORKNAME, SHORTNAME, ADDRESS, WWW, PHONE, FAX)
{
    sc = $('#search_company').val();
    if(sc == placeholder)
        sc = '';
          
    $.post("/bitrix/templates/individ/ajax/ajax_company_set.php", {
        'SEARCH_COMPANY':sc,
        'UF_COMPANY':0,
        'ajax':'y',
        'WORKNAME': WORKNAME,
        'SHORTNAME': SHORTNAME,
        'ADDRESS': ADDRESS,
        'WWW': WWW,
        'PHONE': $PHONE,
        'FAX': FAX,
        }, function(data){ 
        if(data.length > 1)    
        {
            $('#OurCompany').html(data);
            $('#OurCompany').show();
            $('#PH').hide(); 
        }
        else
        {
            $('#OurCompany').html('');
            $('#OurCompany').hide();
            $('#PH').show(); 
        }
    });    
}


function sr_acivediv(pos, id){
	i=1;
	while (document.getElementById("ajax_sr_"+i))
    {
		if(i==pos)
        {
			document.getElementById("ajax_sr_"+i).className = "optionDivSelected";		
		}
        else
        {
			document.getElementById("ajax_sr_"+i).className = "optionDiv";
		}
		i++;
	}
	sr_ajax_show_win ();
}

function sr_active(val, id) {
	
	textval = trim(document.getElementById("ajax_sr_"+val).innerHTML)
	document.getElementById("search_company").value = textval;
	if (document.getElementById("sr_ie_select_fix_help")) 
        document.getElementById("sr_ie_select_fix_help").style.display = "none";
	document.getElementById("ajax_listOfOptions_help").style.display = "none";
	
    SetCompanyFromSearch(id);
    
	/*var data = $.ajax({
		type: "POST",
		url: "/bitrix/templates/individ/ajax/ajax_company_new.php",
		data: "id="+id,
		async: false
	}).responseText;
	
	//document.getElementById("ajax_company_new").innerHTML = data;
	
	arCompany=data.split('|');

	document.forms["regform"].elements["WORKNAME]"].value=trim(arCompany[0]);
	document.forms["regform"].elements["SHORTNAME"].value=trim(arCompany[1]);
	document.forms["regform"].elements["ADDRESS"].value=trim(arCompany[2]);
	document.forms["regform"].elements["WWW"].value=trim(arCompany[3]);
	document.forms["regform"].elements["PHONE"].value=trim(arCompany[4]);
	document.forms["regform"].elements["FAX"].value=trim(arCompany[5]);*/
}

function sr_ajax_show_go(str) {
	
	var data = $.ajax({
		type: "POST",
		url: "/bitrix/templates/individ/ajax/ajax_company_search_reg.php",
		data: "str="+urlencode(str),
		async: false
	}).responseText;
	
	document.getElementById("ajax_listOfOptions").style.display = "block";
	document.getElementById("sr_ajax_container").innerHTML = data;
}

function sr_ajax_show_help_go(str) {
	
	var data = $.ajax({
		type: "POST",
		url: "/bitrix/templates/individ/ajax/ajax_company_help.php",
		data: "str="+urlencode(str),
		async: false
	}).responseText;
	
	if (data.indexOf("NORESULT")){
		if (document.getElementById("sr_ie_select_fix_help")) document.getElementById("sr_ie_select_fix_help").style.display = "block";
		document.getElementById("ajax_listOfOptions_help").style.display = "block";
		document.getElementById("sr_ajax_container_help").innerHTML = data;
	}else{
		if (document.getElementById("sr_ie_select_fix_help")) 
			document.getElementById("sr_ie_select_fix_help").style.display = "none";
		document.getElementById("ajax_listOfOptions_help").style.display = "none";
	}
}

var timeoutsr = null;
function sr_ajax_show_win (){
	//alert ("Y");
	if (document.getElementById("sr_ie_select_fix_help")) 
        document.getElementById("sr_ie_select_fix_help").style.display = "block";
	document.getElementById("ajax_listOfOptions_help").style.display = "block";
	if (timeoutsr) clearTimeout(timeoutsr);
}
function sr_ajax_hide (){
	//alert ("N");
	if (timeoutsr) clearTimeout(timeoutsr);
	timeoutsr = setTimeout(function() { sr_ajax_hide_go() }, 500);
}
function sr_ajax_hide_go (){
	if (document.getElementById("sr_ie_select_fix_help")) document.getElementById("sr_ie_select_fix_help").style.display = "none";
	document.getElementById("ajax_listOfOptions_help").style.display = "none";
}

var timeout = null;

function sr_ajax_show(xstr, e) {
	sr_ajax_show_go(xstr);
}

function sr_ajax_show_help(xstr, e) {
	
	if(e.keyCode!=40 && e.keyCode!=38)
	{
		if (timeout) clearTimeout(timeout);
        if(xstr.length > 1)
		timeout = setTimeout(function() { sr_ajax_show_help_go(xstr) }, 500);
	}
}

/*---------------------подсказка для должности-----------------------------*/


function sr_active_work_position(val) {
	
	textval = trim(document.getElementById("ajax_sr_work_position_"+val).innerHTML)
	document.getElementById("WORK_POSITION").value = textval;
	if (document.getElementById("sr_ie_select_fix_work_position")) document.getElementById("sr_ie_select_fix_work_position").style.display = "none";
	document.getElementById("ajax_listOfOptions_work_position").style.display = "none";
}

function sr_ajax_show_work_position_go(str) {
	
	var data = $.ajax({
		type: "POST",
		url: "/bitrix/templates/individ/ajax/ajax_work_position.php",
		data: "str="+urlencode(str),
		async: false
	}).responseText;
	
	if (data.indexOf("NORESULT")){
		if (document.getElementById("sr_ie_select_fix_work_position")) document.getElementById("sr_ie_select_fix_work_position").style.display = "block";
		document.getElementById("ajax_listOfOptions_work_position").style.display = "block";
		document.getElementById("sr_ajax_container_work_position").innerHTML = data;
	}else{
		if (document.getElementById("sr_ie_select_fix_work_position")) 
			document.getElementById("sr_ie_select_fix_work_position").style.display = "none";
		document.getElementById("ajax_listOfOptions_work_position").style.display = "none";
	}
}

var timeoutsr = null;
function sr_ajax_show_win_work_position (){
	//alert ("Y");
	if (document.getElementById("sr_ie_select_fix_work_position")) document.getElementById("sr_ie_select_fix_work_position").style.display = "block";
	document.getElementById("ajax_listOfOptions_work_position").style.display = "block";
	if (timeoutsr) clearTimeout(timeoutsr);
}
function sr_ajax_hide_work_position (){
	//alert ("N");
	if (timeoutsr) clearTimeout(timeoutsr);
	timeoutsr = setTimeout(function() { sr_ajax_hide_go_work_position() }, 500);
}
function sr_ajax_hide_go_work_position (){
	if (document.getElementById("sr_ie_select_fix_work_position")) document.getElementById("sr_ie_select_fix_work_position").style.display = "none";
	document.getElementById("ajax_listOfOptions_work_position").style.display = "none";
}


var timeout = null;

function sr_ajax_show_work_position(xstr, e) {
	sr_ajax_show_go_work_position(xstr);
}

function sr_ajax_show_work_position(xstr, e) {
	
	if(e.keyCode!=40 && e.keyCode!=38)
	{
		if (timeout) clearTimeout(timeout);
		timeout = setTimeout(function() { sr_ajax_show_go_work_position(xstr) }, 500);
	}
}


function sr_ajax_show_work_position(xstr, e) {
	
	if(e.keyCode!=40 && e.keyCode!=38)
	{
		if (timeout) clearTimeout(timeout);
		timeout = setTimeout(function() { sr_ajax_show_work_position_go(xstr) }, 500);
	}
}

/*--------------------------------------------------------------*/


/*---------------------подсказка для титула-----------------------------*/


function sr_active_work_profile(val) {
	
	textval = trim(document.getElementById("ajax_sr_work_profile_"+val).innerHTML)
	document.getElementById("WORK_PROFILE").value = textval;
	if (document.getElementById("sr_ie_select_fix_work_profile")) document.getElementById("sr_ie_select_fix_work_profile").style.display = "none";
	document.getElementById("ajax_listOfOptions_work_profile").style.display = "none";
}

function sr_ajax_show_work_profile_go(str) {
	
	var data = $.ajax({
		type: "POST",
		url: "/bitrix/templates/individ/ajax/ajax_work_profile.php",
		data: "str="+urlencode(str),
		async: false
	}).responseText;
	
	if (data.indexOf("NORESULT")){
		if (document.getElementById("sr_ie_select_fix_work_profile")) document.getElementById("sr_ie_select_fix_work_profile").style.display = "block";
		document.getElementById("ajax_listOfOptions_work_profile").style.display = "block";
		document.getElementById("sr_ajax_container_work_profile").innerHTML = data;
	}else{
		if (document.getElementById("sr_ie_select_fix_work_profile")) 
			document.getElementById("sr_ie_select_fix_work_profile").style.display = "none";
		document.getElementById("ajax_listOfOptions_work_profile").style.display = "none";
	}
}

var timeoutsr = null;
function sr_ajax_show_win_work_profile (){
	//alert ("Y");
	if (document.getElementById("sr_ie_select_fix_work_profile")) document.getElementById("sr_ie_select_fix_work_profile").style.display = "block";
	document.getElementById("ajax_listOfOptions_work_profile").style.display = "block";
	if (timeoutsr) clearTimeout(timeoutsr);
}
function sr_ajax_hide_work_profile (){
	//alert ("N");
	if (timeoutsr) clearTimeout(timeoutsr);
	timeoutsr = setTimeout(function() { sr_ajax_hide_go_work_profile() }, 500);
}
function sr_ajax_hide_go_work_profile (){
	if (document.getElementById("sr_ie_select_fix_work_profile")) document.getElementById("sr_ie_select_fix_work_profile").style.display = "none";
	document.getElementById("ajax_listOfOptions_work_profile").style.display = "none";
}


var timeout = null;

function sr_ajax_show_work_profile(xstr, e) {
	sr_ajax_show_go_work_profile(xstr);
}

function sr_ajax_show_work_profile(xstr, e) {
	
	if(e.keyCode!=40 && e.keyCode!=38)
	{
		if (timeout) clearTimeout(timeout);
		timeout = setTimeout(function() { sr_ajax_show_go_work_profile(xstr) }, 500);
	}
}


function sr_ajax_show_work_profile(xstr, e) {
	
	if(e.keyCode!=40 && e.keyCode!=38)
	{
		if (timeout) clearTimeout(timeout);
		timeout = setTimeout(function() { sr_ajax_show_work_profile_go(xstr) }, 500);
	}
}

/*--------------------------------------------------------------*/




function urlencode( str ) {
	var ret = str;     
	ret = ret.toString();    
	ret = encodeURIComponent(ret);    
	ret = ret.replace(/%20/g, '+');
	return ret;
}



function trim(string){
	return string.replace(/(^\s+)|(\s+$)/g, "");
}
