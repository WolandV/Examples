<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
* Звания -варианты значений
*/

foreach(array("UF_STATION","UF_GRADE") as $field){
    /**
    * обязательное
    */
if (is_callable(array($arResult["USER_PROPERTIES"]["DATA"][$field]["USER_TYPE"]['CLASS_NAME'], 'getlist')))
    {
        $enum = array();

        $rsEnum=CUserTypeEnum::GetList($arResult["USER_PROPERTIES"]["DATA"][$field]);


        while($arEnum = $rsEnum->GetNext())
        {
            $enum[$arEnum["SORT"]]=array(
                "ID"=>$arEnum["ID"],
                "NAME"=> $arEnum["VALUE"],
            );
        }
        krsort($enum);
        $arResult["USER_PROPERTIES"]["DATA"][$field]["USER_TYPE"]["FIELDS"] = array_values($enum);
        
    }
}
/*-----------Сортируем-----------------------*/
	
foreach ($arResult["SHOW_FIELDS"] as $val)	{
	
	if (is_array($val)) {	
		if ($val["FIELD_NAME"]=="UF_STATION") $arResult_new["SHOW_FIELDS"][61]=$val;
		if ($val["FIELD_NAME"]=="UF_STATION2") $arResult_new["SHOW_FIELDS"][62]=$val;
		if ($val["FIELD_NAME"]=="UF_GRADE") $arResult_new["SHOW_FIELDS"][63]=$val;
		if ($val["FIELD_NAME"]=="UF_GRADE2") $arResult_new["SHOW_FIELDS"][64]=$val;
	}
	else 
	{		
		if ($val=="LOGIN") $arResult_new["SHOW_FIELDS"][10]=$val;
		if ($val=="PASSWORD") $arResult_new["SHOW_FIELDS"][20]=$val;
		if ($val=="CONFIRM_PASSWORD") $arResult_new["SHOW_FIELDS"][30]=$val;
		
		if ($val=="LAST_NAME") $arResult_new["SHOW_FIELDS"][40]=$val;
		if ($val=="NAME") $arResult_new["SHOW_FIELDS"][50]=$val;
		if ($val=="SECOND_NAME") $arResult_new["SHOW_FIELDS"][60]=$val;
		if ($val=="WORK_POSITION") $arResult_new["SHOW_FIELDS"][70]=$val;
		
        if ($val=="WORK_PROFILE") $arResult_new["SHOW_FIELDS"][65]=$val;
		if ($val=="PERSONAL_PHOTO") $arResult_new["SHOW_FIELDS"][67]=$val;
		
		
		if ($val=="EMAIL") $arResult_new["SHOW_FIELDS"][120]=$val;	
				
		
	}
	
}

	$arResult["SHOW_FIELDS"]=$arResult_new["SHOW_FIELDS"];
	
	ksort($arResult["SHOW_FIELDS"]);
	
/*-------------------------------------------*/


//echo "<pre>"; print_r($arParams); print_r($arResult); echo "</pre>";

if (count($arResult["ERRORS"]) > 0)
{
	foreach ($arResult["ERRORS"] as $key => $error)
	{
		if (intval($key) <= 0) $arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", GetMessage("REGISTER_FIELD_".$key), $error);
	}

	ShowError(implode("<br />", $arResult["ERRORS"]));
}
elseif($arResult["USE_EMAIL_CONFIRMATION"] === "Y")
{
	?><p><?echo GetMessage("REGISTER_EMAIL_WILL_BE_SENT")?></p><?
}?>

<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="regform" enctype="multipart/form-data">
<?
if (strlen($arResult["BACKURL"]) > 0)
{
?>
	<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
<?
}
?>

<div class="i_form w100">
<div class="i_form_t">
<div class="i_form_b">
<div class="i_form_l">
<div class="i_form_r">
<div class="i_form_tl">
<div class="i_form_tr">
<div class="i_form_bl">
<div class="i_form_br">
<div class="i_form_cont">

<?//xvar_dump($arResult["SHOW_FIELDS"]);?>

<?foreach ($arResult["SHOW_FIELDS"] as $FIELD):?>

	<?if($FIELD=="LOGIN"):?><div class="title">Авторизационные данные</div><?endif;?>
	<?if($FIELD=="LAST_NAME"):?><div class="title top_margin">Публичные персональные данные</div><?endif;?>
	<?if($FIELD=="WORK_POSITION"):?><div class="title top_margin">Публичные профессиональные данные</div><?endif;?>
	<?if($FIELD=="PERSONAL_STREET"):?><div class="title top_margin">Координаты для связи</div><?endif;?>
	<?if($FIELD["FIELD_NAME"]=="UF_HOTEL"):?><div class="title top_margin">Информация о заезде</div><?endif;?>

    <?
    /**
    * Прошу выводить поля "Звание (иное)" и "Степень (иное)" только после выбора в селектах Звание и Степень значения "иное (самостоятельный ввод)"
    */
    
    $notshow=false;
    if (in_array($FIELD["FIELD_NAME"],array("UF_STATION2","UF_GRADE2")))
        $notshow=true;
    
    /**
    * Убираю дефотное значение
    */
    if (in_array($FIELD["FIELD_NAME"],array("UF_STATION","UF_GRADE")))
        $FIELD["MANDATORY"]="Y";

    /**
    * Степень
    */
    if ($FIELD["FIELD_NAME"]=="UF_STATION2" && $_POST["UF_STATION"] && $_POST["UF_STATION"]==$arResult["USER_PROPERTIES"]["DATA"]["UF_STATION"]["USER_TYPE"]['FIELDS'][0]['ID']){
        $notshow=false;
    }
   /**
   * Звание        
   */
    if ($FIELD["FIELD_NAME"]=="UF_GRADE2" && $_POST["UF_GRADE"] && $_POST["UF_GRADE"]==$arResult["USER_PROPERTIES"]["DATA"]["UF_GRADE"]["USER_TYPE"]['FIELDS'][0]['ID']){
        $notshow=false;
    }        
    ?>
    <div id="div-<?=$FIELD["FIELD_NAME"]?>"<?if ($notshow):?> style="display: none;"<?endif?>>

		<?
		/*if (is_string($arResult["FORM_ERRORS"]) && strlen($arResult["FORM_ERRORS"])>0 && strpos($arResult["FORM_ERRORS"], '"'.$arQuestion["CAPTION"].'"') !== false)
		{
			$f_class="f_err";
		}
		else*/if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
			$f_class="f_req2";
		}else{
			$f_class="f_lite";
		}	
		
		if ($FIELD["MANDATORY"]=="Y") $f_class="f_req2";
		?>
		
		<div class="fname">			
				<?
							
				if (is_array($FIELD)):?>
					<?if($FIELD["FIELD_NAME"]=="UF_SECTION"):?>
						<br/><strong><?echo "Данные, относящиеся к конференции:"?></strong><br/><br/>
					<?endif;?>					
					<?=$FIELD["EDIT_FORM_LABEL"]?>:					
				<?else:?>					
					<?=GetMessage("REGISTER_FIELD_".$FIELD)?>:
				<?endif;?>
		</div>
		<div class="<?=$f_class?>"><?
	switch ($FIELD)
	{
		
		case "PASSWORD":
		case "CONFIRM_PASSWORD":
			?><input size="30" class="inputtext" type="password" name="REGISTER[<?=$FIELD?>]" /><?
		break;

		case "PERSONAL_GENDER":
			?><select name="REGISTER[<?=$FIELD?>]">
				<option value=""><?=GetMessage("USER_DONT_KNOW")?></option>
				<option value="M"<?=$arResult["VALUES"][$FIELD] == "M" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_MALE")?></option>
				<option value="F"<?=$arResult["VALUES"][$FIELD] == "F" ? " selected=\"selected\"" : ""?>><?=GetMessage("USER_FEMALE")?></option>
			</select><?
		break;

		case "PERSONAL_COUNTRY":
			//echo "<pre>"; print_r($arResult["COUNTRIES"]); echo "</pre>";
			?><select name="REGISTER[<?=$FIELD?>]"><?
			foreach ($arResult["COUNTRIES"]["reference_id"] as $key => $value)
			{
				?><option value="<?=$value?>"<?if ($value == $arResult["VALUES"][$FIELD]):?> selected="selected"<?endif?>><?=$arResult["COUNTRIES"]["reference"][$key]?></option>
			<?
			}
			?></select><?
		break;

		case "PERSONAL_PHOTO":
		case "WORK_LOGO":
			?><input size="30" class="inputfile" type="file" name="REGISTER_FILES_<?=$FIELD?>" /><?
		break;

		case "PERSONAL_NOTES":
		case "WORK_NOTES":
			?><textarea class="textarea" rows="10" name="REGISTER[<?=$FIELD?>]"><?=$arResult["VALUES"][$FIELD]?></textarea><?
		break;
		
		case "SEARCH_COMPANY":
			?>
			<div style="position: relative; margin-bottom: 8px;">
			<input class="inputtext" size="30" type="text" name="SEARCH_COMPANY" value="<?$_REQUEST["SEARCH_COMPANY"]?>"
					type="text" id="search_company"	

					onfocus="if (this.value=='Поиск компании') this.value='';" 
					onblur="if(this.value =='') this.value='Поиск компании'" 
					onkeyup="sr_ajax_show_help(this.value, event)"											
					autocomplete="off"/>
		
			<!--[if lte IE 6.5]><div class="sr_ie_select_fix_help dnone" id="sr_ie_select_fix_help"><iframe></iframe><![endif]-->
			<div id="ajax_listOfOptions_help" onmouseout="sr_ajax_hide()" onmouseover="sr_ajax_show_win()">
				<div id="sr_ajax_container_help">
					&nbsp;
				</div>
			</div>
			<!--[if lte IE 6.5]></div><![endif]-->
			</div>
			
			<input class="button-85" type="button" name="button_search_company" value="Поиск" onclick="sr_ajax_show(document.forms['regform'].search_company.value, event)" />
					
			<?
		break;
		
		case "WORK_POSITION":
			?>
			<div style="position: relative;">
			<input class="inputtext" size="30" type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>"
					type="text" id="WORK_POSITION"	
					onkeyup="sr_ajax_show_work_position(this.value, event)"
					autocomplete="off"/>
		
			<!--[if lte IE 6.5]><div class="sr_ie_select_fix_help dnone" id="sr_ie_select_fix_help"><iframe></iframe><![endif]-->
			<div id="ajax_listOfOptions_work_position" onmouseout="sr_ajax_hide_work_position()" onmouseover="sr_ajax_show_win_work_position()">
				<div id="sr_ajax_container_work_position">
					&nbsp;
				</div>
			</div>
			<!--[if lte IE 6.5]></div><![endif]-->
			</div>	
			<?
		break;
		
		case "WORK_PROFILE":
			?>
			<div style="position: relative;">
			<input class="inputtext" size="30" type="text" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>"
					type="text" id="WORK_PROFILE"	
					onkeyup="sr_ajax_show_work_profile(this.value, event)"
					autocomplete="off"/>
		
			<!--[if lte IE 6.5]><div class="sr_ie_select_fix_help dnone" id="sr_ie_select_fix_help"><iframe></iframe><![endif]-->
			<div id="ajax_listOfOptions_work_profile" onmouseout="sr_ajax_hide_work_profile()" onmouseover="sr_ajax_show_win_work_profile()">
				<div id="sr_ajax_container_work_profile">
					&nbsp;
				</div>
			</div>
			<!--[if lte IE 6.5]></div><![endif]-->
			</div>	
			<?
		break;
		
		case is_array($FIELD):?>
									
			<?$APPLICATION->IncludeComponent(
				"bitrix:system.field.edit",
				$FIELD["USER_TYPE"]["USER_TYPE_ID"],
				array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $FIELD, "form_name" => "regform"), null, array("HIDE_ICONS"=>"Y"));			
		break;
		
		
		default:
			if ($FIELD == "PERSONAL_BIRTHDAY"):?><small><?=$arResult["DATE_FORMAT"]?></small><br /><?endif;
			
			
			?><input size="30" type="text" class="inputtext<?=$class_multisele?>" name="REGISTER[<?=$FIELD?>]" value="<?=$arResult["VALUES"][$FIELD]?>"<?if (in_array($FIELD,array("NAME","LAST_NAME"))):?> maxlength="50"<?endif?><?if (in_array($FIELD,array("PERSONAL_ZIP","PERSONAL_WWW"))):?> maxlength="255"<?endif?>/><?
				if ($FIELD == "PERSONAL_BIRTHDAY")
					$APPLICATION->IncludeComponent(
						'bitrix:main.calendar',
						'',
						array(
							'SHOW_INPUT' => 'N',
							'FORM_NAME' => 'regform',
							'INPUT_NAME' => 'REGISTER[PERSONAL_BIRTHDAY]',
							'SHOW_TIME' => 'N'
						)
					);//echo Calendar("REGISTER[PERSONAL_BIRTHDAY]", "regform");
				?><?
	
	}?></div>
		<?if($FIELD=="LOGIN"):?><span class="grey-10 mar-bottom dblock">Минимальная длина - 3 символа</span><?endif;?>
        <?if($FIELD=="PASSWORD"):?><span class="grey-10 mar-bottom dblock">Пароль должен быть не менее <?=GetPassMinLength()?> символов длиной.</span><?endif;?>
	
		<?if ($FIELD=="SEARCH_COMPANY"):?>				
		
			
			<div id="ajax_listOfOptions">
				<div id="sr_ajax_container">
					&nbsp;
				</div>
			
			<?if (strlen($_REQUEST["WORKNAME"])>0):?>
			<script type="text/javascript">			
				sr_ajax_show_go("<?=$_REQUEST["WORKNAME"]?>|<?=$_REQUEST["SHORTNAME"]?>|<?=$_REQUEST["ADDRESS"]?>|<?=$_REQUEST["WWW"]?>|<?=$_REQUEST["PHONE"]?>|<?=$_REQUEST["FAX"]?>");
			</script>
			<?endif;?>		
		
		</div>
					
		<?endif;?>
	</div>	
<?endforeach?>
<?
$usergroups=cuser::GetUserGroupArray();
$subscribe_access =array();
if(CModule::IncludeModule("ito"))
    $subscribe_access = @unserialize(COption::GetOptionString("ito", "subscribe_access"));    
$arID=array();
foreach ($subscribe_access as $ID=>$arGroups){
    if (count(array_intersect($arGroups,$usergroups))>0){
        $arID[]=$ID;
    }
}    
?>
<?if($arResult['arSubscrRubric'] && count($arID)>0):?>
<div class="title top_margin">Подписка и рассылки</div>
<?

foreach($arResult['arSubscrRubric'] as $arRubric)
{   
    if (in_array($arRubric["ID"],$arID)){
        $checked = ( !isset($arResult['VALUES']['SubscrRubric']) || in_array($arRubric['ID'], $arResult['VALUES']['SubscrRubric'])  ? 'checked' : '');
        ?><input type="checkbox" class="" <?=$checked?> name="SubscrRubric[]" value="<?=$arRubric['ID']?>" id="SubscrRubric_<?=$arRubric['ID']?>"> <label for="SubscrRubric_<?=$arRubric['ID']?>"><?=$arRubric['NAME']?></label><br/><?
    }
}
?><br />
<?endif;?>

<?
/* CAPTCHA */
if ($arResult["USE_CAPTCHA"] == "Y")
{
	?>
		<div class="fname">
			<?=GetMessage("REGISTER_CAPTCHA_TITLE")?>
		</div>
		<div>
				<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
				<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />	
		</div>
		<div class="fname"><?=GetMessage("REGISTER_CAPTCHA_PROMT")?></div>
		<div class="f_req"><input type="text" class="inputtext" name="captcha_word" maxlength="50" value="" /></div>		
	<?
}
/* CAPTCHA */
?>	
	<?/*<div><?echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"];?></div>*/?>
	<div class="form_foot">
		<div class="buttons">
			<input type="submit" class="button-120" name="register_submit_button" value="<?=GetMessage("AUTH_REGISTER")?>" />
		</div>
		
		<div class="required_info">
			<?=GetMessage("FORM_REQUIRED_FIELDS")?>
		</div>
	</div>

</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

</form>
<script type="text/javascript">
$(document).ready(function(){
    $("select[name=UF_STATION]").change(function(){
        var show=$(this).val()==$('option:last',this).val()
        $('#div-UF_STATION2').css({display:(show?'block':'none')})
    })
    $("select[name=UF_GRADE]").change(function(){
        var show=$(this).val()==$('option:last',this).val()
        $('#div-UF_GRADE2').css({display:(show?'block':'none')})
    })    
});
</script>