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
<div class="city-select" data-module="city-select">
    <script type="text/x-config">
		{
			"url": "<?=$arParams["CUR_DIR"]?>",
			"cities": [
                <?foreach($arResult["ITEMS"] as $key=>$arItem):?>
				    { "id": <?=$arItem["ID"]?>, "text": "<?=$arItem["NAME"]?>" }<?if($key<count($arResult["ITEMS"])-1):?>,<?endif;?>
                <?endforeach;?>
			],
			"showCityRequestPopover": false
		}
    </script>
    <form action="<?=$arParams["CUR_DIR"]?>" method="POST" id="city-select-form">
        <select name="city-select" id="_id-city-select">
            <?foreach($arResult["ITEMS"] as $arItem):?>
            	<?
            	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            	
                if($arParams["CITY_GEO"]["ID"]==$arItem["ID"])
                {
                    $is_selected = 'selected';
                }else{
                    $is_selected = '';
                }
                if($arParams["CITY_GEO"]["ID"]==$arItem["ID"])
                {
                    ?>
                	<option value="<?=$arItem["ID"]?>" id="<?=$this->GetEditAreaId($arItem['ID']);?>" <?=$is_selected?>>
                        <?=$arItem["NAME"]?>
                    </option>
                    <?
                }
             endforeach;?>
        </select>
        <input type="hidden" name="city-id" value="<?=$arParams["CITY_GEO"]["ID"]?>" id="city-select-value" />
        <?=bitrix_sessid_post()?>
    </form>
</div>