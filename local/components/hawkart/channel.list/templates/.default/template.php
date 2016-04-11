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
global $USER;
?>

<?
/*** В настройки компонента
** "AJAX" => $_REQUEST["AJAX"],
** "LIST_URL" => $APPLICATION->GetCurDir()
**/

// номер текущей страницы
$curPage = $arResult["NAV_RESULT"]->NavPageNomer;
// всего страниц - номер последней страницы
$totalPages = $arResult["NAV_RESULT"]->NavPageCount;
$curPage++;
?>

<section class="broadcast-results" data-module="broadcast-results">
    <script type="text/x-config">
    {
        "recordingURL": "<?=SITE_TEMPLATE_PATH?>/ajax/to_record.php",
        "fetchResultsURL" : "<?=$arParams["LIST_URL"]?>",
        "page": "<?=$curPage?>",
        "ajaxType": "CHANNELS",
        "dates" : [
            <?$key = 1;foreach($arResult["CONFIG_DATES"] as $date){?>
            {
                "dayReq": "<?=$date?>",
                "dayMark": "<?=CTimeEx::dateToStrWithDay($date);?>"
            }<?if($key<count($arResult["CONFIG_DATES"])):?>,<?endif; $key++;}?>
        ]
    }
    </script>
    
	<div class="categories-logos">
        <?foreach($arResult["CHANNELS"] as $arItem):?>
        	<?
        	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        	
            if(!in_array($arItem['ID'], $arResult["CHANNELS_SHOW"]) && $USER->IsAuthorized()) continue;
            ?>
    		<a class="category-logo" href="<?=$arItem["DETAIL_PAGE_URL"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
    			<span data-icon="<?=$arItem["PROPERTIES"]["ICON"]["VALUE"]?>"></span>
    		</a>
        <?endforeach?>
        <?foreach($arResult["SOCIAL_CHANNELS"] as $arItem):?>
            <a class="category-logo" href="#">
    			<span data-icon="<?=$arItem["PROPERTIES"]["ICON"]["VALUE"]?>"><?=$arItem["NAME"]?></span>
    		</a>
        <?endforeach?>
	</div>
    
    <a href="#" class="prev-button" data-type="prev-button">
		<span class="sticky-wrapp">
			<span class="prev-date"><?=CTimeEx::dateToStrWithDay($arResult["FIRST_DATE"]);?></span>
			<span data-icon="icon-kinetic-arrow"></span>
		</span>
	</a>
    
	<div class="categories-items kinetic-active">
        <div class="canvas-wrap">
            <div class="left-days-placeholder"></div>
            <?
            $date_count = 1;
            foreach($arResult["DATES"] as $date => $arChannels)
            {
                ?>
                <div class="day">
                    <?
                    $first = false;
                    
                    foreach($arResult["CHANNELS"] as $arChannel)
                    {
                        $channel = $arChannel["ID"];
                        $arProgs = $arChannels[$channel];
                        
                        if(!in_array($channel, $arResult["CHANNELS_SHOW"]) && $USER->IsAuthorized())
                            continue;

                        ?>
                        <div class="category-row">
                            <?
                            if(!$first)
                            {
                                $arParams["NEED_POINTER"] = true;
                                $first = true;
                            }
                            $notShow = array();
                            foreach($arProgs as $key=>$arProg)
                            {
                                if(in_array($key, $notShow))
                                    continue;
                                    
                                if($arProg["CLASS"]=="one" || $arProg["CLASS"]=="double")
                                {
                                    echo CProgTime::getProgInfoIndex($arProg, $arParams);
                                }
        
                                if($arProg["CLASS"]=="half")
                                {
                                    $arProgNext = $arProgs[$key+1];
                                    ?>
                                    <div class="pair-container">
                                        <?=CProgTime::getProgInfoIndex($arProg, $arParams)?>
                                        <?=CProgTime::getProgInfoIndex($arProgNext, $arParams)?>
                    				</div>
                                    <?
                                    $notShow[]=$key+1;
                                }
                            }
                            unset($arParams["NEED_POINTER"]);
                            ?>
                        </div>
                        <?
                        
                        $next_date = date('d.m.Y', strtotime("+1 day", strtotime($date)));
                    }
                    
                    foreach($arResult["SOCIAL_CHANNELS"] as $arChannel)
                    {
                        $socialChannel = $arChannel["ID"];
                        $arProgs = $arChannels[$socialChannel];
                        ?>
                        <div class="category-row">
                            <?
                            if(!$first)
                            {
                                $arParams["NEED_POINTER"] = true;
                                $first = true;
                            }
                            $notShow = array();
                            foreach($arProgs as $key=>$arProg)
                            {
                                echo CProgTime::getSocialProgInfoIndex($arProg, $socialChannel);
                            }
                            unset($arParams["NEED_POINTER"]);
                            ?>
                        </div>
                        <?
                    }

                    //if($date_count<count($arResult["DATES"]))
                    //{
                        ?>
                        <div class="day-mark">
                            <span>
                                <span class="current-day"><?=CTimeEx::dateToStrWithDay($date);?></span>
                                <span class="next-day"><?=CTimeEx::dateToStrWithDay($next_date);?></span>
                            </span>
                        </div>
                        <?
                    //}
                    ?>
                </div>
                <?
                $date_count++;
            }
            ?>
            <div class="right-days-placeholder"></div>
            
        </div>
    </div><!-- /.categories-items -->
    
    <?
    $next_date = date('d.m.Y', strtotime("+1 day", strtotime($arResult["FIRST_DATE"])));
    ?>
    <a href="#" class="next-button" data-type="next-button">
		<span class="sticky-wrapp">
			<span class="next-date"><?=CTimeEx::dateToStrWithDay($next_date);?></span>
			<span data-icon="icon-kinetic-arrow"></span>
		</span>
	</a>

    <?/*if($arParams["DISPLAY_BOTTOM_PAGER"] == "Y" && $totalPages>1):?>
        <a href="#" class="more-link" id="channels-show-ajax-link" data-load="<?=$arParams["LIST_URL"]?>" data-page="<?=$curPage?>" data-ajax-type="CHANNELS" data-type="fetch-results-link">Показать еще каналы <span data-icon="icon-show-more-arrow"></span></a>
    <?endif;*/?>
</section>