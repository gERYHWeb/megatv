<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER;
$arResult["CHANNELS"] = array();
$selectedChannels = array();
$arChannels = array();

//get subsribe channel list
$result = \Hawkart\Megatv\SubscribeTable::getList(array(
    'filter' => array("UF_ACTIVE"=>1, "=UF_USER_ID" => $USER->GetID()),
    'select' => array("UF_CHANNEL_ID")
));
while ($arSub = $result->fetch())
{
    $selectedChannels[] = $arSub["UF_CHANNEL_ID"];
}

$result = \Hawkart\Megatv\ChannelBaseTable::getList(array(
    'filter' => array("UF_ACTIVE" => 1),
    'select' => array("ID", "UF_TITLE", "UF_ICON", "UF_PRICE_H24")
));
while ($arChannel = $result->fetch())
{
    if(in_array($arChannel["ID"], $selectedChannels))
        $arChannel["SELECTED"] = true;

    $arChannels[] = $arChannel;
}

$arResult["CHANNELS"] = $arChannels;

$this->IncludeComponentTemplate();
?>