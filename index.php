<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Программа телепередач на сегодня - ТВ программа в Москве на МегаТВ, записи телепередач онлайн");
$APPLICATION->SetDirProperty("h1", "Программа телепередач на сегодня");
$APPLICATION->SetDirProperty("h1-hide", "");
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