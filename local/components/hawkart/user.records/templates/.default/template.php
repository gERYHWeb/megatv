<?
if(count($arResult["RECORDS"])==0)
    return false;
?>
<div class="broadcasts-categories" data-module="broadcasts-categories">
	<script type="text/x-config">
		{
			"url": "/personal/records/"
		}
	</script>
	<div class="items">
		<a href="#" class="item active" data-type="item" data-category="all">
			Все
		</a>
        <?
        foreach($arResult["CATEGORIES"] as $category=> $translit)
        {
            ?>
            <a href="#<?=$translit?>" class="item" data-type="item" data-category="<?=$translit?>">
    			<?=$category?>
    		</a>
            <?
        }
        ?>
	</div>
	<div class="more" data-type="more">
		<span data-icon="icon-close"></span>
		<div class="circle"></div>
		<div class="circle"></div>
		<div class="circle"></div>
	</div>
</div>


<section class="broadcasts" data-module="user-recorded-broadcasts">
    <script type="text/x-config">
		{
			"url": "<?=SITE_TEMPLATE_PATH?>/ajax/delete_record.php"
		}
	</script>
    
	<div class="broadcasts-list">
        <?
        foreach($arResult["RECORDS"] as $arRecord)
        {
            $datetime = $arRecord['UF_DATE_START']->toString();
            $date = substr($datetime, 0, 10);
            $time = substr($datetime, 11, 5);
            if(strlen($arRecord["UF_NAME"])>25)
            {
                $arRecord["UF_NAME"] = substr($arRecord["UF_NAME"], 0, 25)."...";
            }
            ?>
            <div class="item<?if($arRecord["UF_WATCHED"]):?> status-viewed<?endif;?>" data-broadcast-id="<?=$arRecord["ID"]?>" data-category="<?=$arResult["CATEGORIES"][$arRecord["UF_CATEGORY"]]?>">
                <div class="inner">
                    <?
                    if($arRecord["UF_WATCHED"])
                    {
                        $path = $_SERVER["DOCUMENT_ROOT"].$arRecord["PICTURE"]["SRC"];
                        ?>
                        <div class="item-image-holder" style="background-image: url(<?=SITE_TEMPLATE_PATH?>/ajax/img_grey.php?path=<?=urlencode($path)?>)"></div>
                        <span class="item-status-icon">
							<span data-icon="icon-viewed"></span>
							<span class="status-desc">Просмотрено</span>
						</span>
                        <?
                    }else{
                        $img = $arRecord["PICTURE"]["SRC"];
                        ?><div class="item-image-holder" style="background-image: url(<?=$img?>)"></div><?
                    }
                    ?>
					
					<div class="actions-panel">
						<ul class="actions-list">
							<li><a href="#" data-type="delete-trigger" title="Удалить"><span data-icon="icon-trash-action"></span></a></li>
							<?/*<li><a href="#" data-type="share-trigger" title="Поделиться"><span data-icon="icon-network-action"></span></a></li>*/?>
							<?if(!empty($arRecord["UF_URL"])):?>
                                <li><a href="#" data-type="player-trigger" title="Просмотреть"><span data-icon="icon-play-action"></span></a></li>
                            <?endif;?>
						</ul>
						<div class="delete-dialog">
							<p>Вы уверены, что хотите удалить данную передачу навсегда?</p>
							<ul>
								<li><a href="#" data-type="delete-broadcast">Да, хочу</a></li>
								<li><a href="#" data-type="cancel-delete-state">Отменить</a></li>
							</ul>
						</div>
					</div>
					<div class="item-header">
						<div class="view-progress" data-progress="<?=intval($arRecord["UF_PROGRESS_PERS"])?>"></div>
						
                        <div class="meta">
							<div class="time"><?=$time?></div>
							<div class="date"><?=$date?></div>
							<div class="category"><a href="#" data-type="category"><?=$arRecord["UF_CATEGORY"]?></a></div>
						</div>
						<div class="title">
							<a href="#"><?=$arRecord["UF_NAME"]?></a>
						</div>
					</div>
                </div>
			</div>
            <?
        }
        ?>
	</div><!-- /.broadcasts-list -->
</section>