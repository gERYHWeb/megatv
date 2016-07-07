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
<section>
    <h2>Сериалы</h2>
    <div class="broadcasts-list">
        <?
        foreach($arResult["ITEMS"] as $arSerial)
        {
            ?>
            <div class="item">
                <div class="inner">
                	<div class="item-header">
                        <div class="title">
                    		<a href="/serials/<?=$arSerial["UF_EPG_ID"]?>/"><?=$arSerial["UF_TITLE"]?></a>
                        </div>
                        <div class="meta">
        					<p><?=$arSerial["UF_DESC"]?></p>
        				</div>
                	</div>
                </div>
            </div>
            <?    
        }
        ?>
    </div>
</section>