<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$smartBase = ($arParams["SEF_URL_TEMPLATES"]["section"]? $arParams["SEF_URL_TEMPLATES"]["section"]: "#CHANNEL_CODE#/");
$arDefaultUrlTemplates404 = array(
	"sections" => "",
	"section" => "#CHANNEL_CODE#/",
	"element" => "#CHANNEL_CODE#/#SCHEDULE_CODE#/"
);

$arDefaultVariableAliases404 = array();

$arDefaultVariableAliases = array();

$arComponentVariables = array(
	"CHANNEL_CODE",
	"SCHEDULE_CODE",
);

if($arParams["SEF_MODE"] == "Y")
{
	$arVariables = array();

	$engine = new CComponentEngine($this);
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

    //FOR SEO
    $url_params = parse_url($_SERVER["REQUEST_URI"]);
    $path = explode("/", $url_params["path"]);
    $arVariables["CHANNEL_CODE"] = $path[2];
    $arVariables["SCHEDULE_CODE"] = $path[3];
    
    
	$componentPage = $engine->guessComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);

	$b404 = false;
    
    //FOR SEO
    if(!empty($arVariables["CHANNEL_CODE"]) && empty($arVariables["SCHEDULE_CODE"]))
    {
        $componentPage = "section";
    }
    if(!empty($arVariables["SCHEDULE_CODE"]))
    {
        $componentPage = "element";
    }
    
    
	if(!$componentPage)
	{
		$componentPage = "sections";
		$b404 = true;
	}

	if($componentPage == "section")
	{
		if (empty($arVariables["CHANNEL_CODE"]))
			$b404 = true;
	}
    
	if($b404 && CModule::IncludeModule('iblock'))
	{
		$folder404 = str_replace("\\", "/", $arParams["SEF_FOLDER"]);
		if ($folder404 != "/")
			$folder404 = "/".trim($folder404, "/ \t\n\r\0\x0B")."/";
		if (substr($folder404, -1) == "/")
			$folder404 .= "index.php";

		if ($folder404 != $APPLICATION->GetCurPage(true))
		{
			\Bitrix\Iblock\Component\Tools::process404(
				""
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SET_STATUS_404"] === "Y")
				,($arParams["SHOW_404"] === "Y")
				,$arParams["FILE_404"]
			);
		}
	}

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
		"FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates,
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}
else
{
	$arVariables = array();

	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";

	$arCompareCommands = array(
		"COMPARE"
	);

    if(isset($arVariables["SCHEDULE_CODE"]) && intval($arVariables["SCHEDULE_CODE"]) > 0)
		$componentPage = "element";
	elseif(isset($arVariables["CHANNEL_CODE"]) && intval($arVariables["CHANNEL_CODE"]) > 0)
		$componentPage = "section";
	else
		$componentPage = "sections";

	$arResult = array(
		"FOLDER" => "",
		"URL_TEMPLATES" => Array(
			"section" => htmlspecialcharsbx($APPLICATION->GetCurPage())."?".$arVariableAliases["CHANNEL_CODE"]."=#CHANNEL_CODE#",
			"element" => htmlspecialcharsbx($APPLICATION->GetCurPage())."?".$arVariableAliases["CHANNEL_CODE"]."=#CHANNEL_CODE#"."&".$arVariableAliases["SCHEDULE_CODE"]."=#SCHEDULE_CODE#"
		),
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases
	);
}
$this->IncludeComponentTemplate($componentPage);
?>