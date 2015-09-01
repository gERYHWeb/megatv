<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeTemplateLangFile(__FILE__);
?>
<!DOCTYPE html>
<html lang="<?=LANGUAGE_ID?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="format-detection" content="telephone=no">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
        <?
        //$APPLICATION->ShowHead();
        echo '<meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'"'.(true ? ' /':'').'>'."\n";
        $APPLICATION->ShowMeta("robots", false, true);
        $APPLICATION->ShowMeta("keywords", false, true);
        $APPLICATION->ShowMeta("description", false, true);
        $APPLICATION->ShowCSS(true, true);
        
        $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/megatv/dist/css/main.css');
        $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/project.css');
        $APPLICATION->ShowHeadStrings();
    	$APPLICATION->ShowHeadScripts();
        
        global $USER;
        session_start();
        if(isset($_POST["city-id"]) && intval($_POST["city-id"])>0 && check_bitrix_sessid())
        {
            CCityEx::setGeoCity(intval($_POST["city-id"]));
        }
        ?>
		<title><?$APPLICATION->ShowTitle()?></title>
	</head>
	<body>
        <div id="panel"><?$APPLICATION->ShowPanel();?></div>
        
        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/header-signin.php"), false);?>
        
		<div class="site-wrapper" data-module="page">

            <script type="text/x-config">
				{
					"bannersHideTime": 1,
					"pathToSVGSprite": "<?=SITE_TEMPLATE_PATH?>/megatv/dist/img/sprites/svg_sprite.svg",
					"playerURL": "modals/player.html",
					"shareURL": "/server/"
				}
			</script>
            
			<div id="drop-area" class="drop-area">
				<div>
					<div class="dropzone">
						<span class="checkmark-holder"></span>
						<span class="dropzone-text">Чтобы добавить, тащи сюда</span>
					</div>
				</div>
			</div>
			<header class="site-header">
				<div class="fullsize-banner adv-styling-01<?if(strpos($_COOKIE['advertizing_hidden_banners'], "header-adv")!==false):?> hide<?endif;?>" data-type="advertizing" id="header-adv">
					<div class="banner-content">
                        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/header-banner.php"), false);?>
					</div>
					<a href="#" class="close-link" data-type="hide-banner-link">Скрыть</a>
				</div>
				<div class="top-panel">
                    <?if($APPLICATION->GetCurDir()=="/"):?>
					   <span class="logo"></span>
                    <?else:?>
                        <a href="/" class="logo"></a>
                    <?endif;?>
                    
                    <?$APPLICATION->IncludeComponent(
                    	"bitrix:news.list",
                    	"cities",
                    	Array(
                    		"DISPLAY_DATE" => "Y",
                    		"DISPLAY_NAME" => "Y",
                    		"DISPLAY_PICTURE" => "Y",
                    		"DISPLAY_PREVIEW_TEXT" => "Y",
                    		"AJAX_MODE" => "N",
                    		"IBLOCK_TYPE" => "directories",
                    		"IBLOCK_ID" => "5",
                    		"NEWS_COUNT" => "100",
                    		"SORT_BY1" => "SORT",
                    		"SORT_ORDER1" => "ASC",
                    		"SORT_BY2" => "NAME",
                    		"SORT_ORDER2" => "ASC",
                    		"FILTER_NAME" => "",
                    		"FIELD_CODE" => array("NAME"),
                    		"PROPERTY_CODE" => array(),
                    		"CHECK_DATES" => "Y",
                    		"DETAIL_URL" => "",
                    		"PREVIEW_TRUNCATE_LEN" => "",
                    		"ACTIVE_DATE_FORMAT" => "d.m.Y",
                    		"SET_TITLE" => "Y",
                    		"SET_BROWSER_TITLE" => "Y",
                    		"SET_META_KEYWORDS" => "Y",
                    		"SET_META_DESCRIPTION" => "Y",
                    		"SET_STATUS_404" => "N",
                    		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
                    		"ADD_SECTIONS_CHAIN" => "Y",
                    		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    		"PARENT_SECTION" => "",
                    		"PARENT_SECTION_CODE" => "",
                    		"INCLUDE_SUBSECTIONS" => "Y",
                    		"CACHE_TYPE" => "A",
                    		"CACHE_TIME" => "36000000",
                    		"CACHE_FILTER" => "N",
                    		"CACHE_GROUPS" => "Y",
                    		"PAGER_TEMPLATE" => "",
                    		"DISPLAY_TOP_PAGER" => "N",
                    		"DISPLAY_BOTTOM_PAGER" => "Y",
                    		"PAGER_TITLE" => "Новости",
                    		"PAGER_SHOW_ALWAYS" => "N",
                    		"PAGER_DESC_NUMBERING" => "N",
                    		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    		"PAGER_SHOW_ALL" => "N",
                    		"AJAX_OPTION_JUMP" => "N",
                    		"AJAX_OPTION_STYLE" => "Y",
                    		"AJAX_OPTION_HISTORY" => "N",
                            "CITY_GEO" => CCityEx::getGeoCity(),
                            "CUR_DIR" => $APPLICATION->GetCurDir()
                    	),
                    false
                    );?>
                    
                    <?require($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/include/header-user-card.php");?>

				</div>
				<div class="bottom-panel">
					<div class="calendar" data-module="calendar">
						<script type="text/x-config">
							{
								"currentDate": "<?=CTimeEx::getCurDate()?>",
								"minDate": 1,
								"maxDate":<?=CTimeEx::getCalendarDays()?>
							}
						</script>
                        <a href="#" data-type="calendar-trigger" class="calendar-trigger"><span><?=CTimeEx::dateToStr()?></span></a>
						<div class="datepicker-holder"></div>
					</div>
					<div class="calendar-carousel" data-module="calendar-carousel">
						<script type="text/x-config">
							{
								"currentDate": "<?=CTimeEx::getCurDate()?>",
								"minDate": 1,
								"maxDate":<?=CTimeEx::getCalendarDays()?>
							}
						</script>
						<a href="#" class="prev-trigger disabled" data-type="prev-trigger"><span data-icon="icon-left-arrow-days"></span></a>
						<div class="dates-holder" data-type="dates-carousel"></div>
						<a href="#" class="next-trigger" data-type="next-trigger"><span data-icon="icon-right-arrow-days"></span></a>
					</div>
                    
                    <?$APPLICATION->IncludeComponent("bitrix:menu","top",Array(
                            "ROOT_MENU_TYPE" => "top", 
                            "MAX_LEVEL" => "1", 
                            "CHILD_MENU_TYPE" => "top", 
                            "USE_EXT" => "Y",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "Y",
                            "MENU_CACHE_TYPE" => "N", 
                            "MENU_CACHE_TIME" => "3600", 
                            "MENU_CACHE_USE_GROUPS" => "Y", 
                            "MENU_CACHE_GET_VARS" => "" 
                        )
                    );?>
                    
                    <?$APPLICATION->IncludeComponent("hawkart:search", "", Array(), false);?>
				</div>
			</header>
			<main class="site-content">