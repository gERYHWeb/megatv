<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

global $USER;
CModule::IncludeModule("iblock");

$progIds = array();
$arFilter = array(
    "UF_USER" => $USER->GetID()
);

/*if($arParams["WATCHED"]=="Y")
{
    $arFilter["!UF_WATCHED"] = false;
}*/

$arFilter["!UF_URL"] = false;

$arRecords = CRecordEx::getList($arFilter, array("ID", "UF_PROG", "UF_URL", "UF_PROGRESS_PERS", 
"UF_NAME", "UF_SUB_TITLE", "UF_PICTURE", "UF_CATEGORY", "UF_WATCHED", "UF_DATE_START"), array("UF_DATE_END"=>"DESC"));

$arResult["RECORDS"] = array();
foreach($arRecords as $arRecord)
{
    $arRecord["NAME"] = CProgTime::cutName($arRecord["NAME"]);
    $arRecord["PICTURE"] = CDev::resizeImage($arRecord["UF_PICTURE"], 300, 300);
    if(!empty($arRecord["UF_CATEGORY"]))
        $arCats[] = $arRecord["UF_CATEGORY"];
    $arResult["RECORDS"][] = $arRecord;
}

$arCats = array_unique($arCats);

$arResult["CATEGORIES"] = array();
foreach($arCats as $category)
{
    $arParams = array("replace_space"=>"-", "replace_other"=>"-");
    $str = CDev::translit($category, "ru", $arParams);
    $arResult["CATEGORIES"][$category] = $str; 
}

$this->IncludeComponentTemplate();
?>