<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<?$APPLICATION->IncludeComponent("hawkart:serial.detail", "", 
    Array(),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>