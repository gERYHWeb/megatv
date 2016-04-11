<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

global $USER;
if(!is_object($USER))
    $USER = new CUser;

if(!$USER->IsAuthorized())
    return false;

/**
 * Показ прямого эфира канала
 */ 
if(isset($_REQUEST["channel_id"]))
{
    $arChannel = CChannel::getByID($_REQUEST["channel_id"], array("NAME", "PROPERTY_STREAM_URL", "PREVIEW_PICTURE"));
    ?>
    <div class="advert-holder">
        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/player-banner.php"), false);?>
    </div>
    <div class="broadcast-player" data-module="broadcast-player">
    	<script type="text/x-config">
    		{
    			"seekTime": "0",
    			"broadcastID": "0",
    			"streamURL": "<?=$arChannel["PROPERTY_STREAM_URL_VALUE"]?>",
    			"posterURL": "<?=CFile::GetPath($arChannel["PREVIEW_PICTURE"])?>",
    			"videoTitle": "<?=$arChannel["NAME"]?>",
                "playerFlashURL": "<?=SITE_TEMPLATE_PATH?>/megatv/app/js/vendors/jwplayer/jwplayer.flash.swf"
    		}
    	</script>
    	<div class="block-header">
    		<h3 class="block-title"><?=$arChannel["NAME"]?></h3>
    	</div>
    	<div class="block-body">
    		<a href="#" class="close-link" data-dismiss="modal"><span data-icon="icon-times"></span></a>
    		<div class="player-holder">
    			<div id="player"></div>
    		</div>
    	</div>
    </div>
    <?
}else{
    
    if(strpos($_GET["broadcastID"], "youtube")!==false || strpos($_GET["broadcastID"], "vk")!==false)
    {
        $id = str_replace(array("youtube|", "vk|"), "", $_GET["broadcastID"]);
        
        if(strpos($_GET["broadcastID"], "youtube")!==false)
        {
            $videos = \YoutubeClient::getList();
        }else{
            $videos = \VkClient::getList();
        }
        foreach($videos as $arVideo)
        {
            if($arVideo["ID"]==$id)
                break;
        }
        
        ?>
        <div class="advert-holder">
            <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/player-banner.php"), false);?>
        </div>
        <div class="broadcast-player" data-module="broadcast-player">
        	<script type="text/x-config">
        		{
        			"seekTime": "0",
        			"broadcastID": "0",
        			"streamURL": "<?=$arVideo["VIDEO_URL"]?>",
        			"posterURL": "<?=$arVideo["PLAYER_BG"]?>",
        			"videoTitle": "<?=htmlspecialchars($arVideo["NAME"])?>",
                    "playerFlashURL": "<?=SITE_TEMPLATE_PATH?>/megatv/app/js/vendors/jwplayer/jwplayer.flash.swf"
        		}
        	</script>
        	<div class="block-header">
        		<h3 class="block-title"><?=$arVideo["NAME"]?></h3>
        	</div>
        	<div class="block-body">
        		<a href="#" class="close-link" data-dismiss="modal"><span data-icon="icon-times"></span></a>
        		<div class="player-holder">
                    <?if(strpos($_GET["broadcastID"], "youtube")!==false):?>
                        <div id="player"></div>
                    <?else:?>
                        <iframe src="<?=$arVideo["VIDEO_URL"]?>" width="896" height="504" frameborder="0" id="vk-player" class="flash"></iframe>
                        <div  style="display: none !important"><div id="player"></div></div>
                        <script>
                            $(function(){
                                $(".close-link").click(function(){
                                    $("#vk-player").remove();
                                });
                            });
                        </script> 
                    <?endif;?>
        		</div>
        	</div>
        </div>
        <?
    }else{
        
        /**
         * Показ передачи
         */ 
        
        $broadcastID = intval($_GET["broadcastID"]);
        
        if($_GET["record"]!="false")
        {
            $arRecord = CRecordEx::getByID($broadcastID, array("ID", "UF_PROG", "UF_URL", "UF_PROGRESS_PERS", "UF_NAME", "UF_SUB_TITLE", "UF_PICTURE_DOUBLE"));
        }else{
            $arRecords = CRecordEx::getList(array("UF_USER"=> $USER->GetID(), "UF_SCHEDULE"=>$broadcastID), array("ID", "UF_PROG", "UF_URL", "UF_PROGRESS_PERS", "UF_NAME", "UF_SUB_TITLE", "UF_PICTURE_DOUBLE"));
            $arRecord = $arRecords[0];
        }
        
        $arRecord["PICTURE"] = CDev::resizeImage($arRecord["UF_PICTURE_DOUBLE"], 896, 504);
        $arWatched = CRecordEx::getList(array("!UF_WATCHED"=> false, "UF_URL"=>$arRecord["UF_URL"]), array("ID"));
        ?>
        <div class="advert-holder">
            <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/player-banner.php"), false);?>
        </div>
        <div class="broadcast-player" data-module="broadcast-player">
        	<script type="text/x-config">
        		{
        			"seekTime": "<?=intval($arRecord["UF_PROGRESS_SECS"])?>",
        			"broadcastID": "<?=$broadcastID?>",
        			"streamURL": "<?=$arRecord["UF_URL"]?>",
        			"posterURL": "<?=$arRecord["PICTURE"]["SRC"]?>",
        			"videoTitle": "<?=$arRecord["UF_NAME"]?><?= $arRecord["UF_SUB_TITLE"] ? " | ".$arRecord["UF_SUB_TITLE"] : "" ?>",
                    "playerFlashURL": "<?=SITE_TEMPLATE_PATH?>/megatv/app/js/vendors/jwplayer/jwplayer.flash.swf"
        		}
        	</script>
        	<div class="block-header">
        		<h3 class="block-title"><?=$arRecord["UF_NAME"]?><?= $arRecord["UF_SUB_TITLE"] ? " <small>|".$arRecord["UF_SUB_TITLE"]."</small>" : "" ?></h3>
        	</div>
        	<div class="block-body">
        		<a href="#" class="close-link" data-dismiss="modal"><span data-icon="icon-times"></span></a>
        		<div class="player-holder">
        			<div id="player"></div>
        			<div class="player-panel">
        				<dl class="view-count">
        					<dt>Просмотров:</dt>
        					<dd><?=intval(count($arWatched))?></dd>
        				</dl>
        				<?/*<dl class="download-count">
        					<dt>Скачиваний:</dt>
        					<dd>9 157 000</dd>
        				</dl>
        				<div class="social-share">
        					<span>Рассказать друзьям:</span>
        					<ul class="socuals-list">
        						<li><a href="#"><span data-icon="icon-vk-social"></span></a></li>
        						<li><a href="#"><span data-icon="icon-fb-social"></span></a></li>
        						<li><a href="#"><span data-icon="icon-tw-social"></span></a></li>
        						<li><a href="#"><span data-icon="icon-gp-social"></span></a></li>
        					</ul>
        				</div>
        				<a href="broadcast-card-recommendate.html" class="btn btn-default"><span data-icon="icon-network-social"></span>Рекомендовать друзьям</a>*/?>
        			</div>
        		</div>
                <?$APPLICATION->IncludeComponent("hawkart:prog.comments", "", Array("PROG_ID"=>$arRecord["UF_PROG"]), false);?>
        	</div>
        </div><!-- .broadcast-player -->
        <?
    }
}
?>
<?die();?>