<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CScheduleTemplate
{
    public static $cacheDir = "ScheduleTemplate";
    
    public static function cutName($title, $len = false)
    {
        if(!$len)
            $len = 40;
        
        if(strlen($title)>$len)
        {
            $title = substr($title, 0, $len)."...";
        }
        
        return $title;
    }
    
    public static function driveNotifyMessage()
    {
        ob_start();
        ?>
        <div class="extend-drive-notify">
			<div class="extend-drive-notify-text-wrap">
				<span data-icon="icon-storage"></span>
				<p>У Вас закончилось свободное место на облачном хранилище МЕГА.ДИСК</p>
				<p><a href="/personal/services/">Заказать дополнительную емкость</a></p>
			</div>
		</div>
        <?
        $message = ob_get_contents();  
        ob_end_clean();
        return $message;
    }
    
    /**
     * "RECORDING"
       "RECORDED"
       "VIEWED"
     */
    public static function status($arProg)
    {
        global $APPLICATION, $USER;
        $arRecordsStatuses = $APPLICATION->GetPageProperty("ar_record_status");
        $arRecordsStatuses = json_decode($arRecordsStatuses, true);
        
        $arSubscriptionChannels = $APPLICATION->GetPageProperty("ar_subs_channels");
        $arSubscriptionChannels = json_decode($arSubscriptionChannels, true);
        
        $schedule = $arProg["ID"];

        //$arDatetime = \CTimeEx::getDatetime();
        //$date_now = \CTimeEx::dateOffset($arDatetime["OFFSET"], date("d.m.Y H:i:s")); 
        //$datetime = $arDatetime["SERVER_DATETIME_WITH_OFFSET"];
        $date_now = date("d.m.Y H:i:s");
        $start = $arProg["DATE_START"];
        $end = $arProg["DATE_END"];
        
        $status = "";
        if(isset($arRecordsStatuses["VIEWED"][$schedule]))
        {
            $status = "viewed";
        }
        else if(isset($arRecordsStatuses["RECORDING"][$schedule]))
        {
            $status = "recording";
        }
        else if(isset($arRecordsStatuses["RECORDED"][$schedule]))
        {
            $status = "recorded";
        }
        else if(in_array($arProg["UF_CHANNEL_ID"], $arSubscriptionChannels) && $USER->IsAuthorized() && \CTimeEx::dateDiff($date_now, $end) || !$USER->IsAuthorized() )
        {
            $status = "recordable";
        }
        
        ob_start();
        if($status == "recording"):?>
            <div class="item-status-icon">
				<span data-icon="icon-recording"></span>
                <span class="status-desc">В записи</span>
			</div>
        <?endif;?>
        <?if($status == "recorded"):?>
            <span class="item-status-icon" href="#">
				<span data-icon="icon-recorded"></span>
				<span class="status-desc">Смотреть</span>
			</span>
        <?endif;?>
        <?if($status == "viewed"):?>
            <span class="item-status-icon">
				<span data-icon="icon-viewed"></span>
				<span class="status-desc">Просмотрено</span>
			</span>
        <?endif;?>
        
        <?if($status == "recordable"):?>
            <span class="item-status-icon">
				<span data-icon="icon-recordit"></span>
                <span class="status-desc">Записать</span>
			</span>
			<?/*<div class="recording-notify">
				<div class="recording-notify-text-wrap">
					<span data-icon="icon-recording-progress"></span>
					<p>Ваша любимая передача<br> поставлена на запись</p>
				</div>
			</div>*/?>
        <?endif;
        
        $content = ob_get_contents();  
        ob_end_clean();
        
        return array(
            "status" => $status,
            "status-icon" => $content
        );
    }
    
    /**
     *  [ID] => 2
        [UF_CODE] => pena-dney-20160409004500-0300
        [UF_DATE_START] => 09.04.2016 00:45:00
        [UF_DATE_END] => 09.04.2016 03:10:00
        [UF_DATE] => 09.04.2016
        [UF_CHANNEL_ID] => 30
        [UF_PROG_ID] => 2
        [UF_TITLE] => Пена дней
        [UF_SUB_TITLE] => 
        [UF_IMG_PATH] => /upload/epg_original/48870.jpg
        [UF_RATING] => 
        [DATE_START] => 09.04.2016 00:45:00
        [DATE_END] => 09.04.2016 03:10:00
        [DATE] => 09.04.2016
        [PROG_ID] => 2
        [DETAIL_PAGE_URL] => /channels/1tv/pena-dney-20160409004500-0300/
     */
    public static function getProgInfoIndex($arProg, $arParams=false)
    {
        if($arProg["CLASS"]=="double")
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(576, 288));
        }
        
        if($arProg["CLASS"]=="one")
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(288, 288));
        }
        
        if($arProg["CLASS"]=="half")
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(288, 144));
        }
        
        $start = $arProg["DATE_START"];
        $end = $arProg["DATE_END"];
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        $datetime = $arParams["DATETIME"]["SERVER_DATETIME_WITH_OFFSET"];
        
        $time_pointer = false;
        if(\CTimeEx::dateDiff($start, $datetime) && \CTimeEx::dateDiff($datetime, $end))
        {
            $time_pointer = true;
        }
        
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if($time_pointer && $arParams["NEED_POINTER"]):?> js-time-pointer<?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?><?if($arProg["CLASS"]=="double"):?> double-item<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["ID"]?>"
        >
            <div class="item-image-holder">
				<img data-src="<?=$arProg["PICTURE"]["SRC"]?>" alt="">
			</div>
            
            <?if($time_pointer):?>
                <span class="badge" data-channel-id="<?=$arProg["UF_CHANNEL_ID"]?>">в эфире</span>
            <?endif;?>
            
            <?=$status_icon?>
            <?//=self::driveNotifyMessage()?>
            
        	<div class="item-header">
        		<time><?=substr($arProg["DATE_START"], 11, 5)?></time>
        		<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg))?>
                </a>
        	</div>
        </div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function getProgInfoRecommend($arProg)
    {
        $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(288, 288));
        $start = $arProg["DATE_START"];
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        $date = substr($start, 0, 10);
        $time = substr($start, 11, 5);
        ob_start();
        ?>
        <div class="item<?if($status=="viewed"):?> status-viewed<?elseif($status):?> status-<?=$status?><?endif;?>" 
            data-type="broadcast"
            data-broadcast-id="<?=$arProg["ID"]?>" data-category="<?=$arProg["CAT_CODE"]?>"
        >
            <div class="inner">
                <?
                if($status=="viewed")
                {
                    $path = $_SERVER["DOCUMENT_ROOT"].$arProg["PICTURE"]["SRC"];
                    ?>
                    <div class="item-image-holder" style="background-image: url(<?=SITE_TEMPLATE_PATH?>/ajax/img_grey.php?path=<?=urlencode($path)?>)"></div>
                    <span class="item-status-icon">
						<span data-icon="icon-viewed"></span>
						<span class="status-desc">Просмотрено</span>
					</span>
                    <?
                }else{
                    $img = $arProg["PICTURE"]["SRC"];
                    ?><div class="item-image-holder" style="background-image: url(<?=$img?>)"></div><?
                }
                ?>
                
                <?=$status_icon?>
                <?=self::driveNotifyMessage()?>
                
                <?if($status=="recording"):?>
                <div class="recording-notify">
                    <div class="recording-notify-text-wrap">
                        <div class="recording-notify-icon">
                        </div>
                        <p>Ваша любимая передача<br> поставлена на запись</p>
                    </div>
                </div>
                <?endif;?>
				
				<div class="item-header">
                    <div class="meta">
						<div class="time"><?=$time?></div>
						<div class="date"><?=$date?></div>
						<div class="category"><a href="#" data-type="category"><?=$arProg["UF_CATEGORY"]?></a></div>
					</div>
					<div class="title">
						<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                            <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg), 35)?>
                        </a>
					</div>
				</div>
            </div>
		</div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function getSocialProgInfoIndex($arProg, $socialChannel)
    {               
        ob_start();
        ?>
        <div class="item status-recorded status-social-v"
            data-type="broadcast" data-broadcast-id="<?=strtolower($socialChannel)?>|<?=$arProg["ID"]?>"
        >
            <div class="item-image-holder">
				<img data-src="<?=$arProg["IMG"]?>" alt="">
			</div>
            
            <span class="item-status-icon" href="#">
				<span data-icon="icon-recorded"></span>
				<span class="status-desc">Смотреть</span>
			</span>
            
        	<div class="item-header">
        		<a href="#">
                    <?=self::cutName($arProg["NAME"], 70)?>
                </a>
        	</div>
        </div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    /**
     *  [ID] => 2
        [UF_CODE] => pena-dney-20160409004500-0300
        [UF_DATE_START] => 09.04.2016 00:45:00
        [UF_DATE_END] => 09.04.2016 03:10:00
        [UF_DATE] => 09.04.2016
        [UF_CHANNEL_ID] => 30
        [UF_PROG_ID] => 2
        [UF_TITLE] => Пена дней
        [UF_SUB_TITLE] => 
        [UF_IMG_PATH] => /upload/epg_original/48870.jpg
        [UF_RATING] => 
        [DATE_START] => 09.04.2016 00:45:00
        [DATE_END] => 09.04.2016 03:10:00
        [DATE] => 09.04.2016
        [PROG_ID] => 2
        [DETAIL_PAGE_URL] => /channels/1tv/pena-dney-20160409004500-0300/
        [UF_HD] => 1
     */
    public static function getProgInfoChannel($arProg, $arParams)
    {   
        if($arProg["CLASS"]=="one")
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(600, 550));
        }
        
        if($arProg["CLASS"]=="half")
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(300, 550));
        }
        
        $start = $arProg["DATE_START"];
        $end = $arProg["DATE_END"];
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        $datetime = $arParams["DATETIME"]["SERVER_DATETIME_WITH_OFFSET"];
        
        $time_pointer = false;
        if(\CTimeEx::dateDiff($start, $datetime) && \CTimeEx::dateDiff($datetime, $end))
        {
            $time_pointer = true;
        }
        
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if($time_pointer && $arParams["NEED_POINTER"]):?> js-time-pointer<?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?><?if($arProg["CLASS"]=="double"):?> double-item<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["ID"]?>"
        >
            <div class="item-image-holder">
				<img data-src="<?=$arProg["PICTURE"]["SRC"]?>" alt="">
			</div>
            
            <?if($time_pointer):?>
                <span class="badge" data-channel-id="<?=$arProg["UF_CHANNEL_ID"]?>">в эфире</span>
            <?endif;?>
            
            <?=$status_icon?>
            <?=self::driveNotifyMessage()?>
            
        	<div class="item-header">
        		<time><?=substr($arProg["DATE_START"], 11, 5)?></time>
        		<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg))?>
                </a>
        	</div>

			<div class="item-header">
                <?if(\CTimeEx::dateDiff($start, $datetime) && \CTimeEx::dateDiff($datetime, $end)):?>
                    <?
                    $allSecs = strtotime($end) - strtotime($start);
                    $secs = strtotime($datetime) - strtotime($start);
                    
                    $proc = ceil($secs/($allSecs/100));
                    $duration = \CTimeEx::secToStr($secs);
                    ?>
    				<div class="timeline" data-progress="<?=$proc?>">
    					<span class="progress-bg"></span>
    					<span>прошло <?=$duration?></span>
    				</div>
                <?endif;?>
				<span class="descr-trigger" data-type="descr-trigger"><span>&times;</span></span>
				<time><?=substr($arProg["DATE_START"], 11, 5)?></time>
				<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg))?>
                </a>
				<div class="item-descr">
                    <?
                    $obParser = new \CTextParser;
                    ?>
					<p><?=$obParser->html_cut($arProg["UF_DESC"], 600)?></p>
				</div>
			</div>
		</div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function getProgInfoRecommendIndex($arProg, $arParams, $key=false)
    {
        if($key<4)
        {
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(300, 300));
        }else{
            $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(600, 600));
        }
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["ID"]?>"
        >
            <div class="item-image-holder" style="background-image: url(<?=$arProg["PICTURE"]["SRC"]?>)"></div>
        	
            <?=$status_icon?>
            <?//=self::driveNotifyMessage()?>
            
            <div class="item-header">
				<time><?=substr($arProg["DATE_START"], 11, 5)?> <span class="date">| <?=substr($arProg["DATE_START"], 0, 10)?></span></time>
				<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg))?>
                </a>
                <?/*if($arParams["NOT_SHOW_CHANNEL"]!="Y"):?>
    				<div class="channel-icon">
    					<span data-icon="<?=$arProg["UF_ICON"]?>" data-size="small"></span>
    				</div>
                <?endif;*/?>
			</div>
        </div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function getProgSimilar($arProg, $arParams)
    {
        $arProg["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arProg["UF_IMG_PATH"], array(300, 300));
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["ID"]?>"
        >
            <div class="item-image-holder" style="background-image: url(<?=$arProg["PICTURE"]["SRC"]?>)"></div>
            
            <?=$status_icon?>
            <?=self::driveNotifyMessage()?>
            
        	<div class="item-header">
        		<time><?=substr($arProg["DATE_START"], 11, 5)?> | <?=substr($arProg["DATE_START"], 0, 10)?></time>
        		<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName(\Hawkart\Megatv\ProgTable::getName($arProg))?>
                </a>
        	</div>
        </div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function updateCache() 
    {
		\CCacheEx::clean(self::$cacheDir);
	}
}