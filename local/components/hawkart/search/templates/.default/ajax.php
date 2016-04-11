<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

$query = htmlspecialcharsbx($_REQUEST["query"]);

$activeChannels = CChannel::getList(array("ACTIVE"=>"Y"), array("ID", "DETAIL_PAGE_URL", "PROPERTY_ICON"));
$ids = array();
$arChannels = array();
foreach($activeChannels as $activeChannel)
{
    $ids[] = $activeChannel["ID"];
    $arChannels[$activeChannel["ID"]] = $activeChannel;
}

CModule::IncludeModule('search');
$obSearch = new CSearch;
$arSearchQuery = array(
    'QUERY' => trim($query),
    "SITE_ID" => 's1'
);
$arSearchSort = array(
    'TITLE_RANK' => 'ASC',
    'DATE_CHANGE' => 'DESC',
    'CUSTOM_RANK' => 'DESC',
    'RANK' => 'DESC',
    //'CUSTOM_RANK' => 'DESC',
    //'DATE_CHANGE' => 'DESC'
);
$arSearchFilter = array(
    array(
       '=MODULE_ID' => 'iblock',
       'PARAM2' => array(PROG_TIME_IB)
    )
);

$obSearch->Search($arSearchQuery, $arSearchSort, $arSearchFilter);
while($arSearch = $obSearch->GetNext()){
    $arSearchResult[] = $arSearch['ITEM_ID'];
};

$arFilter = array(
    'ID' => $arSearchResult
);

/*$arQuery = array();
$explode = explode(" ", trim($query));
foreach($explode as $val)
{
    if(!empty($val))
       $arQuery[] = trim($val); 
}

if(count($arQuery)==1)
{
   $arFilter["?NAME"] = $arQuery;
}else{
    $arFilter = array(
        "LOGIC"=>"OR",
    );
    foreach($arQuery as $val)
    {
        $arFilter[] = array("?NAME"=>$val); 
    }
}*/

$arProgs = CProg::getList(false, array("ID", "PREVIEW_PICTURE", "NAME", "PROPERTY_SUB_TITLE", "PROPERTY_CHANNEL")); 
$arProgsSorted = array();
foreach($arProgs as $arProg)
{
    $arProgsSorted[$arProg["ID"]] = $arProg;
}
unset($arProgs);

$filterDateStart = CTimeEx::datetimeForFilter(date("Y-m-d H:i:s"));
$arProgTimes = CProgTime::getList(array(
    //">=PROPERTY_DATE_START" => $filterDateStart,
    "PROPERTY_CHANNEL" => $ids,
    'ID' => $arSearchResult
), array("ID", "PROPERTY_DATE_START", "PROPERTY_DATE_END", "PROPERTY_PROG", "PROPERTY_CHANNEL", "DETAIL_PAGE_URL"));


$arTime = CTimeEx::getDatetime();
$arResult = array();
foreach($arProgTimes as $arSchedule)
{
    $progID = $arSchedule["PROPERTY_PROG_VALUE"];
    if(isset($arProgsSorted[$progID]))
    {
        $arProg = $arProgsSorted[$progID];
        $channel = $arSchedule["PROPERTY_CHANNEL_VALUE"];
        
        $arJson = array();
        $date = CTimeEx::dateOffset($arTime["OFFSET"], $arSchedule["PROPERTY_DATE_START_VALUE"]);
        $name = $arProg["NAME"];
        if($arProg["PROPERTY_SUB_TITLE_VALUE"])
            $name.= ". ".$arProg["PROPERTY_SUB_TITLE_VALUE"];
        
        $arJson["date"] = substr($date, 11, 5)." | ".substr($date, 0, 10);
        $arJson["title"] = $name;
        if($arProg["PREVIEW_PICTURE"])
        {
            $arPic = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 60, 60);
            $arJson["thumbnail"] = $arPic["SRC"];
        }
        else
        {
            $arJson["thumbnail"] = "null";
        }
            
        $arJson["tokens"] = array();
        $arJson["link"] = $arChannels[$channel]["DETAIL_PAGE_URL"].$arSchedule["CODE"]."/";
        
        $arResult[] = $arJson;
    }
}

exit(json_encode($arResult));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>