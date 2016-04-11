<?
IncludeModuleLangFile(__FILE__);
class CProgTime
{
    public static $cacheDir = "prog_time";
    
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
    
    public static function generateUnique($arFields)
    {
        if(strlen($arFields["DATE_START"])==10)
            $arFields["DATE_START"].= " 00:00:00";
        
        $arFields["DATE_START"] = preg_replace("/[^0-9]/", '', $arFields["DATE_START"]);

        $str = $arFields["CHANNEL"].$arFields["DATE_START"];
        return $str;
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
    
    public static function getByID($ID, $arSelect = false)
    {
        CModule::IncludeModule("iblock");
        
        if(!$ID)
            return false;
        
        if(empty($arSelect))
            $arSelect = Array("ID", "NAME");
        
        $arProgs = array();
        $arFilter = array("IBLOCK_ID" => PROG_TIME_IB, "ACTIVE" => "Y", "=ID" => $ID);
        
        $CacheEx = new CCacheEx(60*60*24*365, self::$cacheDir);
        $arProg = $CacheEx->cacheElement( array( "SORT" => "ASC", "ID" => "DESC" ), $arFilter, "getlist", false, $arSelect);
        
        return $arProg[0];
    }
    
    public static function getList($arrFilter = false, $arSelect = array())
    {
        CModule::IncludeModule("iblock");
        $arProgTimes = array();
        
        if(empty($arSelect))
            $arSelect = Array("ID", "NAME", "PROPERTY_CHANNEL", "PROPERTY_DATE_START");
            
        $arFilter = array("IBLOCK_ID" => PROG_TIME_IB, "ACTIVE" => "Y");
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);

        
        $CacheEx = new CCacheEx(60*60*24*2, self::$cacheDir);
        $arTmpProgTimes = $CacheEx->cacheElement( array( "PROPERTY_DATE_START" => "ASC"), $arFilter, "getlist", false, $arSelect);
        foreach( $arTmpProgTimes as $arTmpProgTime )
        {
            if($arTmpProgTime["PROPERTY_CHANNEL_VALUE"] && $arTmpProgTime["PROPERTY_DATE_START_VALUE"])
            {
                $unique = self::generateUnique(array(
                    "CHANNEL" => $arTmpProgTime["PROPERTY_CHANNEL_VALUE"],
                    "DATE_START" => $arTmpProgTime["PROPERTY_DATE_START_VALUE"],
                ));
                $arProgTimes[$unique] = $arTmpProgTime;
            }else{
                $arProgTimes[] = $arTmpProgTime;
            }
		}
        
        return $arProgTimes;
    }
    
    public static function add($arFields)
    {
        CModule::IncludeModule("iblock");
        $el = new CIBlockElement;
        
        $PROP = array();
        $PROP = $arFields["PROPS"];
        $PROP["DATE_START"] = date("d.m.Y H:i:s", strtotime($PROP["DATE_START"]));
        $PROP["DATE_END"] = date("d.m.Y H:i:s", strtotime($PROP["DATE_END"]));
        
        //$datetime = date("dmY-Hi", strtotime($PROP["DATE_START"]));
        $datetime = preg_replace("/[^0-9]/", '', $PROP["DATE_START"]);
        $date = substr($datetime, 0, 2).substr($datetime, 2, 2);
        $time = substr($datetime, 8, 2).substr($datetime, 10, 2);
        
        if(strlen($time)==2) $time.="00";
        
        $arFields["FIELDS"]["NAME"] = trim($arFields["FIELDS"]["NAME"]);
        $arParams = array("replace_space"=>"-", "replace_other"=>"-");
        $translit = CDev::translit(trim($date."-".$time."-".$PROP["CHANNEL"]."-".$arFields["FIELDS"]["NAME"]), "ru", $arParams);
        
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => PROG_TIME_IB,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => $arFields["FIELDS"]["NAME"],
            "CODE"           => $translit,   
            "ACTIVE"         => "Y",
        );
        
        $arLoadProductArray = array_merge($arLoadProductArray, $arFields["FIELDS"]);
        $progTimeID = $el->Add($arLoadProductArray);
        if($progTimeID)
        {
            return $progTimeID;
        }else{
            
            CDev::log(array(
                "ERROR" => $el->LAST_ERROR,
                "PROG" => $arLoadProductArray+array("DATE_TIME"=>$datetime),
            ));
            
            return $el->LAST_ERROR;
        }
    }
    
    public static function delete($arrFilter = false) 
    {
		CModule::IncludeModule("iblock");
        $arProgTimes = array();
        $arSelect = Array("ID");
        $arFilter = array("IBLOCK_ID" => PROG_TIME_IB, "ACTIVE" => "Y", "<PROPERTY_DATE"=>date('Y-m-d', strtotime('-1 day'))); 
        
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        $rsRes = CIBlockElement::GetList( array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
		while( $arItem = $rsRes->GetNext() )
        {
            CIBlockElement::Delete($arItem["ID"]);
		}
        
        self::updateCache();
	}
    
    
    public static function deleteNotInFile($arScheduleIdsNotDelete, $arrFilter = false) 
    {
		CModule::IncludeModule("iblock");
        $arProgTimes = array();
        $arSelect = Array("ID");
        $arFilter = array("IBLOCK_ID" => PROG_TIME_IB, "ACTIVE" => "Y", ">=PROPERTY_DATE" => date('Y-m-d')); 
        
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        $rsRes = CIBlockElement::GetList( array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
		while( $arItem = $rsRes->GetNext() )
        {
            if(!in_array($arItem["ID"], $arScheduleIdsNotDelete) && !empty($arScheduleIdsNotDelete))
            {
                CIBlockElement::Delete($arItem["ID"]);
            }
		}
        
        self::updateCache();
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
        
        $schedule = $arProg["SCHEDULE_ID"];

        $arDatetime = CTimeEx::getDatetime();
        $date_now = CTimeEx::dateOffset($arDatetime["OFFSET"], date("d.m.Y H:i:s")); 
        $datetime = $arDatetime["SERVER_DATETIME_WITH_OFFSET"];
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
        else if(in_array($arProg["CHANNEL_ID"], $arSubscriptionChannels) && $USER->IsAuthorized() && CTimeEx::dateDiff($date_now, $end) || !$USER->IsAuthorized() )
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
    
    public static function getProgInfoIndex($arProg, $arParams=false)
    {
        if($arProg["CLASS"]=="double")
        {
            $arProg["PICTURE"]["SRC"] = CFile::GetPath($arProg["PROPERTY_PICTURE_DOUBLE_VALUE"]);
        }
        
        if($arProg["CLASS"]=="one")
        {
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 288, 288);
        }
        
        if($arProg["CLASS"]=="half")
        {
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PROPERTY_PICTURE_HALF_VALUE"], 288, 144);
        }
        
        $start = $arProg["DATE_START"];
        $end = $arProg["DATE_END"];
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        $datetime = $arParams["DATETIME"]["SERVER_DATETIME_WITH_OFFSET"];
        
        $time_pointer = false;
        if(CTimeEx::dateDiff($start, $datetime) && CTimeEx::dateDiff($datetime, $end))
        {
            $time_pointer = true;
        }
        
        if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"]))
            $arProg["NAME"].= " | ".$arProg["PROPERTY_SUB_TITLE_VALUE"];
        
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if($time_pointer && $arParams["NEED_POINTER"]):?> js-time-pointer<?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?><?if($arProg["CLASS"]=="double"):?> double-item<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["SCHEDULE_ID"]?>"
        >
            <div class="item-image-holder">
				<img data-src="<?=$arProg["PICTURE"]["SRC"]?>" alt="">
			</div>
            
            <?if($time_pointer):?>
                <span class="badge" data-channel-id="<?=$arProg["CHANNEL_ID"]?>">в эфире</span>
            <?endif;?>
            
            <?=$status_icon?>
            <?//=self::driveNotifyMessage()?>
            
        	<div class="item-header">
        		<time><?=substr($arProg["DATE_START"], 11, 5)?></time>
        		<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName($arProg["NAME"])?>
                    <?/*if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"])):?>.<br><?=$arProg["PROPERTY_SUB_TITLE_VALUE"]?><?endif;*/?> 
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
        $arProg["PICTURE"] = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 288, 288);
        $start = $arProg["DATE_START"];

        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"]))
            $arProg["NAME"].= " | ".$arProg["PROPERTY_SUB_TITLE_VALUE"];
        
        $date = substr($start, 0, 10);
        $time = substr($start, 11, 5);
        
        ob_start();
        ?>
        <div class="item<?if($status=="viewed"):?> status-viewed<?elseif($status=="recording"):?> recording-in-progress<?else:?> status-<?=$status?><?endif;?>" 
            data-broadcast-id="<?=$arProg["SCHEDULE_ID"]?>" data-category="<?=$arProg["CAT_CODE"]?>"
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
				
				<div class="item-header">
                    <div class="meta">
						<div class="time"><?=$time?></div>
						<div class="date"><?=$date?></div>
						<div class="category"><a href="#" data-type="category"><?=$arProg["CATEGORY"]?></a></div>
					</div>
					<div class="title">
						<a href="<?=$arProg["DETAIL_PAGE_URL"]?>"><?=self::cutName($arProg["NAME"])?></a>
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
    
    public static function getProgInfoChannel($arProg, $arParams)
    {        
        if($arProg["CLASS"]=="one")
        {
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PROPERTY_PICTURE_VERTICAL_DOUBLE_VALUE"], 600, 550);
        }
        
        if($arProg["CLASS"]=="half")
        {
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PROPERTY_PICTURE_VERTICAL_VALUE"], 300, 550);
        }
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        
        $start = $arProg["DATE_START"];
        $end = $arProg["DATE_END"];
        $datetime = $arParams["DATETIME"]["SERVER_DATETIME_WITH_OFFSET"];
        
        if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"]))
            $arProg["NAME"].= " | ".$arProg["PROPERTY_SUB_TITLE_VALUE"];
               
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if($arProg["CLASS"]=="half"):?> half-item<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["SCHEDULE_ID"]?>"
        >
			<div class="item-image-holder" style="background-image: url(<?=$arProg["PICTURE"]["SRC"]?>)"></div>
			
            <?if(CTimeEx::dateDiff($start, $datetime) && CTimeEx::dateDiff($datetime, $end)):?>
                <span class="badge">в эфире</span>
            <?endif;?>
            
            <?if($arProg["PROPERTY_HD_VALUE"]):?>
                <span class="badge">HD</span>
            <?endif;?>
            
			<?=$status_icon?>
            <?=self::driveNotifyMessage()?>
            
			<div class="item-header">
                <?if(CTimeEx::dateDiff($start, $datetime) && CTimeEx::dateDiff($datetime, $end)):?>
                    <?
                    $allSecs = strtotime($end) - strtotime($start);
                    $secs = strtotime($datetime) - strtotime($start);
                    
                    $proc = ceil($secs/($allSecs/100));
                    $duration = CTimeEx::secToStr($secs);
                    ?>
    				<div class="timeline" data-progress="<?=$proc?>">
    					<span class="progress-bg"></span>
    					<span>прошло <?=$duration?></span>
    				</div>
                <?endif;?>
				<span class="descr-trigger" data-type="descr-trigger"><span>&times;</span></span>
				<time><?=substr($arProg["DATE_START"], 11, 5)?></time>
				<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName($arProg["NAME"])?>
                    <?/*if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"])):?>.<br><?=$arProg["PROPERTY_SUB_TITLE_VALUE"]?><?endif;*/?>
                    
                    <?if(!empty($arProg["PROPERTY_YEAR_VALUE"])):?>.(<?=$arProg["PROPERTY_YEAR_VALUE"]?>)<?endif;?>
                </a>
				<div class="item-descr">
                    <?
                    $arProg["PREVIEW_TEXT"] = strip_tags($arProg["PREVIEW_TEXT"]);
                    if(strlen($arProg["PREVIEW_TEXT"]) > 600)
                        $arProg["PREVIEW_TEXT"] = substr($arProg["PREVIEW_TEXT"], 0, 600)."...";
                    ?>
					<p><?=$arProg["PREVIEW_TEXT"]?></p>
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
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 300, 300);
        }else{
            $arProg["PICTURE"] = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 600, 600);
        }
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["SCHEDULE_ID"]?>"
        >
            <div class="item-image-holder" style="background-image: url(<?=$arProg["PICTURE"]["SRC"]?>)"></div>
        	
            <?=$status_icon?>
            <?=self::driveNotifyMessage()?>
            
            <div class="item-header">
				<time><?=substr($arProg["DATE_START"], 11, 5)?> <span class="date">| <?=substr($arProg["DATE_START"], 0, 10)?></span></time>
				<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName($arProg["NAME"])?>
                    <?/*if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"])):?>.<br><?=$arProg["PROPERTY_SUB_TITLE_VALUE"]?><?endif;*/?>
                </a>
                <?/*if($arParams["NOT_SHOW_CHANNEL"]!="Y"):?>
    				<div class="channel-icon">
    					<span data-icon="<?=$arProg["CHANNEL"]["PROPERTY_ICON_VALUE"]?>" data-size="small"></span>
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
        $arProg["PICTURE"] = CDev::resizeImage($arProg["PREVIEW_PICTURE"], 300, 300);
        
        $arStatus = self::status($arProg);
        $status = $arStatus["status"];
        $status_icon = $arStatus["status-icon"];
        ob_start();
        ?>
        <div class="item<?if($status):?> status-<?=$status?><?endif;?><?if(empty($arProg["PICTURE"]["SRC"])):?> is-noimage<?endif;?>"
            data-type="broadcast" data-broadcast-id="<?=$arProg["SCHEDULE_ID"]?>"
        >
			<div class="item-image-holder" style="background-image: url(<?=$arProg["PICTURE"]["SRC"]?>)"></div>
            
			<?=$status_icon?>
            <?=self::driveNotifyMessage()?>
            
			<div class="item-header">
				<time><?=substr($arProg["DATE_START"], 11, 5)?> | <?=substr($arProg["DATE_START"], 0, 10)?></time>
				<a href="<?=$arProg["DETAIL_PAGE_URL"]?>">
                    <?=self::cutName($arProg["NAME"])?>
                    <?/*if(!empty($arProg["PROPERTY_SUB_TITLE_VALUE"])):?>.<br><?=$arProg["PROPERTY_SUB_TITLE_VALUE"]?><?endif;*/?>
                </a>
			</div>
		</div>
        <?
        $content = ob_get_contents();  
        ob_end_clean();
        
        return $content;
    }
    
    public static function getFilterByChannel($arDatetime = array(), $channelId)
    {
        $arFilter = array();
        $arProgTimes = CProgTime::getList(
            array(
                "PROPERTY_DATE" => substr(date("Y-m-d", strtotime($arDatetime["SELECTED_DATE"])), 0, 10),
                "PROPERTY_CHANNEL" => $channelId
            ),
            array(
                "ID", "PROPERTY_DATE_START", "PROPERTY_DATE_END", "PROPERTY_CHANNEL"
            )
        );
        
        $arFirst = array_shift($arProgTimes);
        $minDate = $arFirst["PROPERTY_DATE_START_VALUE"];
        
        $arLast = array_pop($arProgTimes); 
        $maxDate = $arLast["PROPERTY_DATE_END_VALUE"];
            
        $minDate = CTimeEx::dateOffset((-1)*$arDatetime["OFFSET"], $minDate);
        $maxDate = CTimeEx::dateOffset((-1)*$arDatetime["OFFSET"], $maxDate); 
        
        //echo $minDate."<br />".$maxDate;
        
        $filterDateStart = date("Y-m-d H:i:s", strtotime("-3 hour", strtotime($minDate)));
        $filterDateEnd = date('Y-m-d H:i:s', strtotime("-3 hour", strtotime($maxDate)));
        
        //echo $filterDateStart."<br />".$filterDateEnd;
               
        $arFilter = array(
            ">=PROPERTY_DATE_START" => $filterDateStart,
            "<PROPERTY_DATE_END" => $filterDateEnd,
            "PROPERTY_CHANNEL" => $channelId
        );
        return $arFilter;       
    }
    
    public static function updateCache() 
    {
		CCacheEx::clean(self::$cacheDir);
	}
}