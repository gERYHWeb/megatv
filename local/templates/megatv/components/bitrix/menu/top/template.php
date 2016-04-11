<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!empty($arResult)):?>
<ul class="sections-menu">

<?
$arIcons = array("channels", /*"themes", */"recommendations", "icon-film-collection");
foreach($arResult as $key=>$arItem):
	if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1) 
		continue;

    if($arItem["TEXT"]=="Мои записи")
    {
        $showMyRecs = true;
        $myRecSelected = $arItem["SELECTED"];
        continue;
    }
    
    if($arItem["SELECTED"]):?>
		<li class="active"><a href="<?=$arItem["LINK"]?>"><span data-icon="icon-<?=$arIcons[$key]?>"></span><span><?=$arItem["TEXT"]?></span></a></li>
	<?else:?>
		<li><a href="<?=$arItem["LINK"]?>"><span data-icon="icon-<?=$arIcons[$key]?>"></span><span><?=$arItem["TEXT"]?></span></a></li>
	<?endif?>
	
<?endforeach?>
    <?if($showMyRecs):?>
        <li class="<?if($arItem["SELECTED"]):?>active <?else:?><?endif;?>sections-menu-recordings"><a href="/personal/records/"><span data-icon="icon-film-collection"></span> <span>Мои записи<span class="count"><?=$APPLICATION->GetPageProperty("ar_record_in_rec")?> из <?=$APPLICATION->GetPageProperty("ar_record_recorded")?></span></span></a></li>
    <?endif;?>
</ul>
<?endif?>