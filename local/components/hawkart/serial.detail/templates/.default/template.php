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
    <h1><?=$arResult["SERIAL"]["UF_TITLE"]?></h1>
        <div style="clear: both;"></div>
        <div class="broadcasts-list">
        <?
        $notShow = array();
        foreach($arResult["ITEMS"] as $key=>$arVideo)
        {
            ?>
            <div class="item status-recorded status-social-v"
                data-type="broadcast" data-broadcast-id="<?=$arVideo["UF_EXTERNAL_ID"]?>"
            >
                <div class="inner">
                    <div class="item-image-holder" style="background-image: url(<?=$arVideo["UF_THUMBNAIL_URL"]?>)"></div>
                    
                    <span class="item-status-icon" href="#">
        				<span data-icon="icon-recorded"></span>
        				<span class="status-desc">Смотреть</span>
        			</span>
                    
                	<div class="item-header">
                        <div class="meta">
    						
    					</div>
                        <div class="title">
                    		<a href="#"><?=$arVideo["UF_TITLE"]?></a>
                        </div>
                	</div>
                </div>
            </div>
            <?
        }
        ?>
    </div>
</section>