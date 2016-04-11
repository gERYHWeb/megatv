<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("MegaTV");
global $USER;
CModule::IncludeModule("iblock");
?>

<?$APPLICATION->IncludeComponent("hawkart:user.records", "", Array("WATCHED"=>"N"), false);?>

<?/*
<div class="fullsize-banner adv-styling-03">
	<div class="banner-content">
        <?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/records-banner-1.php"), false);?>
	</div>
</div>

<?
$arRecords = CRecordEx::getList($arFilter, array("UF_PROG"));
if(count($arRecords)>0)
{
    foreach($arRecords as $arRecord)
    {
        $progIds[] = $arRecord["UF_PROG"];
    }
    $progIds = array_unique($progIds);
}

//Темы программы
$arProgs = CProg::getList(array(
    "ID"=>$progIds, 
    "PROPERTY_CHANNEL"=> CIBlockElement::SubQuery(
        "ID",
        array(
            "IBLOCK_ID" => CHANNEL_IB,
            "ACTIVE" => "Y"
        )
	)
), array("PROPERTY_TOPIC", "PROPERTY_CATEGORY"));
foreach($arProgs as $arProg)
{
    $arTopicsExp = explode(",", $arProg["PROPERTY_TOPIC_VALUE"]);
    foreach($arTopicsExp as $key=>$topic)
    {
        if(!empty($topic))
            $arTopics[] = trim($topic);
    }
    
    $arCatsExp = explode(",", $arProg["PROPERTY_CATEGORY_VALUE"]);
    foreach($arCatsExp as $key=>$topic)
    {
        if(!empty($topic))
            $arCats[] = trim($topic);
    }
}

global $arRecommendFilter;
$arRecommendFilter[">=PROPERTY_DATE_START"] = date("Y-m-d H:i:s");
$arRecommendFilter["PROPERTY_PROG"] = CIBlockElement::SubQuery(
    "ID",
    array(
        "IBLOCK_ID" => PROG_IB,
        "ACTIVE" => "Y",
        "?PROPERTY_TOPIC" => $arTopics, 
        "?PROPERTY_CATEGORY" => $arCats
    )
);
?>

<?$APPLICATION->IncludeComponent("bitrix:news.list", "similar", Array(
    "DISPLAY_DATE" => "Y",	// Выводить дату элемента
	"DISPLAY_NAME" => "Y",	// Выводить название элемента
	"DISPLAY_PICTURE" => "Y",	// Выводить изображение для анонса
	"DISPLAY_PREVIEW_TEXT" => "Y",	// Выводить текст анонса
	"AJAX_MODE" => "N",	// Включить режим AJAX
	"IBLOCK_TYPE" => "directories",	// Тип информационного блока (используется только для проверки)
	"IBLOCK_ID" => "8",	// Код информационного блока
	"NEWS_COUNT" => "20",	// Количество новостей на странице
	"SORT_BY1" => "PROPERTY_DATE_START",	// Поле для первой сортировки новостей
	"SORT_ORDER1" => "ASC",	// Направление для первой сортировки новостей
	"SORT_BY2" => "NAME",	// Поле для второй сортировки новостей
	"SORT_ORDER2" => "ASC",	// Направление для второй сортировки новостей
	"FILTER_NAME" => "arRecommendFilter",	// Фильтр
	"FIELD_CODE" => array(	// Поля
		0 => "NAME",
		1 => "ID",
        2 => "CODE"
	),
	"PROPERTY_CODE" => array(	// Свойства
		0 => "DATE_START",
		1 => "DATE_END",
		2 => "CHANNEL",
        3 => "PROG"
	),
	"CHECK_DATES" => "Y",	// Показывать только активные на данный момент элементы
	"DETAIL_URL" => "",	// URL страницы детального просмотра (по умолчанию - из настроек инфоблока)
	"PREVIEW_TRUNCATE_LEN" => "",	// Максимальная длина анонса для вывода (только для типа текст)
	"ACTIVE_DATE_FORMAT" => "d.m.Y",	// Формат показа даты
	"SET_TITLE" => "N",	// Устанавливать заголовок страницы
	"SET_BROWSER_TITLE" => "N",	// Устанавливать заголовок окна браузера
	"SET_META_KEYWORDS" => "N",	// Устанавливать ключевые слова страницы
	"SET_META_DESCRIPTION" => "N",	// Устанавливать описание страницы
	"SET_STATUS_404" => "N",	// Устанавливать статус 404
	"INCLUDE_IBLOCK_INTO_CHAIN" => "N",	// Включать инфоблок в цепочку навигации
	"ADD_SECTIONS_CHAIN" => "N",	// Включать раздел в цепочку навигации
	"HIDE_LINK_WHEN_NO_DETAIL" => "N",	// Скрывать ссылку, если нет детального описания
	"PARENT_SECTION" => "",	// ID раздела
	"PARENT_SECTION_CODE" => "",	// Код раздела
	"INCLUDE_SUBSECTIONS" => "Y",	// Показывать элементы подразделов раздела
	"CACHE_TYPE" => "A",	// Тип кеширования
	"CACHE_TIME" => "36000000",	// Время кеширования (сек.)
	"CACHE_FILTER" => "N",	// Кешировать при установленном фильтре
	"CACHE_GROUPS" => "Y",	// Учитывать права доступа
	"PAGER_TEMPLATE" => "",	// Шаблон постраничной навигации
	"DISPLAY_TOP_PAGER" => "N",	// Выводить над списком
	"DISPLAY_BOTTOM_PAGER" => "N",                                    //
	"PAGER_TITLE" => "Новости",	// Название категорий
	"PAGER_SHOW_ALWAYS" => "N",	// Выводить всегда
	"PAGER_DESC_NUMBERING" => "N",	// Использовать обратную навигацию
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",	// Время кеширования страниц для обратной навигации
	"PAGER_SHOW_ALL" => "N",	// Показывать ссылку "Все"
	"AJAX_OPTION_JUMP" => "N",	// Включить прокрутку к началу компонента
	"AJAX_OPTION_STYLE" => "Y",	// Включить подгрузку стилей
	"AJAX_OPTION_HISTORY" => "N",	// Включить эмуляцию навигации браузера
	"COMPONENT_TEMPLATE" => ".default",
	"AJAX_OPTION_ADDITIONAL" => "",	// Дополнительный идентификатор
	"SET_LAST_MODIFIED" => "N",	// Устанавливать в заголовках ответа время модификации страницы
	"PAGER_BASE_LINK_ENABLE" => "N",	// Включить обработку ссылок
	"SHOW_404" => "N",	// Показ специальной страницы
	"MESSAGE_404" => "",	// Сообщение для показа (по умолчанию из компонента)
    "DATETIME" => CTimeEx::getDatetime(),
    "CITY" => CCityEx::getGeoCity(),
    "TITLE" => "Рекомендованные Передачи"
	),
	false
);?>

<?$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/records-banner-2.php"), false);?>
*/?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>