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
<section class="recommended-broadcasts" data-module="broadcast-results">
    <script type="text/x-config">
    {
        "recordingURL": "<?=SITE_TEMPLATE_PATH?>/ajax/to_record.php"
    }
    </script>
    <div class="broadcasts-list">
        <?
        $notShow = array();
        foreach($arResult["PROGS"] as $key=>$arProg)
        {
            $arProg["CAT_CODE"] = $arResult["CATEGORIES"][$arProg["UF_CATEGORY"]];
            echo \Hawkart\Megatv\CScheduleTemplate::getSocialProgInfoChannel($arProg, "YOUTUBE|".$arResult['UF_CHANNEL_BASE_ID']);
            //echo \Hawkart\Megatv\CScheduleTemplate::getProgInfoRecommend($arProg);
        }
        ?>
    </div>
</section>

<div class="fullsize-banner adv-styling-02">
	<div class="banner-content">
		<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/channel-banner.php"), false);?>
	</div>
</div>

<div class="channel-desc">
    <?=htmlspecialchars_decode($arResult["UF_DESC"]);?>
</div>