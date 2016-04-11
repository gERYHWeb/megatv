<?
$_SERVER["DOCUMENT_ROOT"] = "/home/d/daotel/MEGATV/public_html"; //изменить на сервере
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: text/html; charset=utf-8');
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');

global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new CUser;

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

CProg::updateCache();
/*       
$arProgs = CProg::getList(array("PREVIEW_PICTURE"=>false, "!PROPERTY_PICTURE_DOUBLE"=>false), array(
    "ID", "NAME", "PREVIEW_TEXT", "PROPERTY_CHANNEL", "PROPERTY_SUB_TITLE", "PREVIEW_PICTURE",
    "PROPERTY_PICTURE_DOUBLE", "PROPERTY_PICTURE_HALF", "PROPERTY_PICTURE_VERTICAL", "PROPERTY_PICTURE_VERTICAL_DOUBLE"
));

foreach($arProgs as $arProg)
{
    echo "<pre>"; print_r($arProg); echo "</pre>";
    
    $arProps = array("PICTURE_DOUBLE", "PICTURE_HALF", "PICTURE_VERTICAL", "PICTURE_VERTICAL_DOUBLE");
    foreach($arProps as $code)
    {
        $value = $arProg["PROPERTY_".$code."_VALUE_ID"];
        CIBlockElement::SetPropertyValueCode($arProg["ID"], $code, Array (
            $value =>  array('del' => 'Y', 'tmp_name' => '') 
        ));
        CFile::Delete($arProg["PROPERTY_".$code."_VALUE"]);
    } 
}*/

/**
 * Delete old files from directories
 */


/**
 * Get pics from progs
 */
$arFilePathes = array();
$arProgs = CProg::getList(false, array(
    "PREVIEW_PICTURE", 'DETAIL_PICTURE', "PROPERTY_PICTURE_DOUBLE", "PROPERTY_PICTURE_HALF", 
    "PROPERTY_PICTURE_VERTICAL", "PROPERTY_PICTURE_VERTICAL_DOUBLE"
));
foreach($arProgs as $arProg)
{
    $arProps = array("PICTURE_DOUBLE", "PICTURE_HALF", "PICTURE_VERTICAL", "PICTURE_VERTICAL_DOUBLE");
    foreach($arProps as $code)
    {
        $value = $arProg["PROPERTY_".$code."_VALUE"];
        $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($value);
        $arFilePathes[] = $path; 
    }
    
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arProg["PREVIEW_PICTURE"]);
    $arFilePathes[] = $path;
    
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arProg["DETAIL_PICTURE"]);
    $arFilePathes[] = $path;
}

/**
 * Get pics from channels
 */
$arChannels = CChannel::getList(false, array("ID", "PREVIEW_PICTURE", 'DETAIL_PICTURE',));
foreach($arChannels as $arChannel)
{
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arChannel["PREVIEW_PICTURE"]);
    $arFilePathes[] = $path;
    
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arChannel["DETAIL_PICTURE"]);
    $arFilePathes[] = $path;
}

/**
 * Get pics from records
 */
$arRecords = CRecordEx::getList($arFilter, array("UF_PICTURE_DOUBLE", "UF_PICTURE"));
foreach($arRecords as $arRecord)
{
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arRecord["UF_PICTURE_DOUBLE"]);
    $arFilePathes[] = $path;
    
    $path = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($arRecord["UF_PICTURE"]);
    $arFilePathes[] = $path;
}

function deleteOldPics($dirPath, $arFilePathes)
{
    if (is_dir($dirPath)) 
    {
        $objects = scandir($dirPath);
        foreach ($objects as $object) 
        {
            if ($object != "." && $object !="..") 
            {
                if (filetype($dirPath . "/" . $object) == "dir") 
                {
                    deleteOldPics($dirPath . "/" . $object, $arFilePathes);
                } else {
                    
                    if(!in_array($dirPath . "/" . $object, $arFilePathes))
                    {
                        echo $dirPath . "/" . $object."<br />";
                        unlink($dirPath . "/" . $object);
                    }
                    
                }
            }
        }
        reset($objects);
        rmdir($dirPath);
    }
}

$dirPath = $_SERVER['DOCUMENT_ROOT'].'/upload/iblock';
deleteOldPics($dirPath, $arFilePathes);

die();
?>