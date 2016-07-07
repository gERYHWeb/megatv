<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Сериалы");
?>

<?$APPLICATION->IncludeComponent("hawkart:serial.list", "", 
    Array(),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>