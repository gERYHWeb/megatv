<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);
?>

<?$APPLICATION->IncludeComponent("hawkart:schedule", "", Array(
    "ELEMENT_CODE" => $arResult["VARIABLES"]["SCHEDULE_CODE"],
    "CHANNEL_CODE" => $arResult["VARIABLES"]["CHANNEL_CODE"],
), false);?>

<div class="fullsize-banner adv-styling-02">
	<div class="banner-content">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/schedule-footer-banner.php"), false);?>
	</div>
</div>

<?$APPLICATION->IncludeComponent("hawkart:schedule.similar", "", Array(
    "ELEMENT_CODE" => $_REQUEST["SCHEDULE_CODE"], 
    "TITLE" => "Похожие передачи"
), false);?>