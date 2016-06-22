<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каналы");
global $USER;
//$USER->Authorize(1);
$arChannels = array();
$result = \Hawkart\Megatv\ChannelTable::getList(array(
	'filter' => array("!UF_EPG_ID" => false),
	'select' => array("UF_EPG_ID", "ID", "UF_ACTIVE")
));
while ($row = $result->fetch())
{
	$arChannels[$row["UF_EPG_ID"]] = $row;
}
print_r($arChannels);
?>

<?$APPLICATION->IncludeComponent("hawkart:channel.list", "", 
    Array(
		"NEWS_COUNT" => "45",
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ),
	false
);?>

<?/*$APPLICATION->IncludeComponent("hawkart:recommendations", "index", Array("NOT_SHOW_CHANNEL"=>"Y", "TEMPLATE" => "MAIN_PAGE"),
	false
);*/?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>