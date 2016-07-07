<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каналы");
?>

<?$APPLICATION->IncludeComponent("hawkart:channel.catalog", "", 
    Array(
		"NEWS_COUNT" => "45",
        "DISPLAY_BOTTOM_PAGER" => "Y",
        "SET_STATUS_404" => "Y",
        "SHOW_404" => "Y",
        "SEF_MODE" => "Y",
        "SEF_FOLDER" => "/channels/",
    ),
	false
);?>

<?/*$APPLICATION->IncludeComponent("hawkart:channel.list", "", 
    Array(
		"NEWS_COUNT" => "45",
        "DISPLAY_BOTTOM_PAGER" => "Y",
    ),
	false
);*/?>

<?/*$APPLICATION->IncludeComponent("hawkart:recommendations", "index", Array("NOT_SHOW_CHANNEL"=>"Y", "TEMPLATE" => "MAIN_PAGE"),
	false
);*/?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>