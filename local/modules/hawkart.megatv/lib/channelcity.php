<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ChannelCityTable extends Entity\DataManager
{
    /**
     * import channel & city info connection
     */
    public static function import()
    {
        $arChannelCity = array();
        $arFilter = array();
        $arSelect = array("ID", "UF_CHANNEL_ID", "UF_CITY_ID");
        $result = ChannelCityTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect
        ));
        while ($arItem = $result->fetch())
        {
            $arChannelCity[$arItem["UF_CHANNEL_ID"]."-".$arItem["UF_CITY_ID"]] = $arItem["ID"];
        }
        
        $arCities = array();
        $arFilter = array(
            "UF_COUNTRY.UF_TITLE" => "Россия", 
            "UF_ACTIVE" => 1
        );
        $arSelect = array("ID", "UF_TITLE");
        $result = CityTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect
        ));
        while ($arCity = $result->fetch())
        {
            $arCities[$arCity["UF_TITLE"]] = $arCity["ID"];
        }
        
        $arChannels = array();
        $arFilter = array();
        $arSelect = array("ID", "UF_EPG_ID");
        $result = ChannelTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect
        ));
        while ($arChannel = $result->fetch())
        {
            $arChannels[$arChannel["UF_EPG_ID"]] = $arChannel["ID"];
        }
        
        $file = $_SERVER["DOCUMENT_ROOT"]."/local/modules/hawkart.megatv/files/channel_citiy.csv";
        $lines = file($file);
        foreach ($lines as $line_num => $line) 
        {
            $arItem = explode(";". $line);
            
            $city = trim($arItem[0]);
            $channel_epg_id = trim($arItem[1]);
            $offset = trim($arItem[2]);
            $orbita = trim($arItem[3]);
            
            $city_id = $arCities[$city];
            $channel_id = $arChannels[$channel_epg_id];
            
            if(intval($arItems[$channel_id."-".$city]["ID"])==0)
            {
                $arFields = array(
                    "UF_CITY_ID" => $city_id,
                    "UF_CHANNEL_ID" => $channel_id,
                    "UF_OFFSET" => $offset,
                    "UF_ORBITA" => $orbita
                );
                $result = ChannelCityTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arItems[$channel_id."-".$city] = $id;
                }
            }
            
        }
    }
    
    
    /**
	 * Set primary key from 1
	 *
	 * @return string
	 */
    public static function updatePrimary()
    {
        return "ALTER TABLE ".self::getTableName()." AUTO_INCREMENT=1";
    }

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_channel_city';
	} 
    
	/**
	 * Returns entity map definition
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
            'UF_CHANNEL_ID' => array(
				'data_type' => 'integer',
				'required'  => true
			),
            'UF_CHANNEL' => array(
				'data_type' => '\Hawkart\Megatv\ChannelTable',
				'reference' => array('=this.UF_CHANNEL_ID' => 'ref.ID'),
			),
            'UF_CITY_ID' => array(
				'data_type' => 'integer',
				'required'  => true
			),
            'UF_CITY' => array(
				'data_type' => '\Hawkart\Megatv\CityTable',
				'reference' => array('=this.UF_CITY_ID' => 'ref.ID'),
			),
            'UF_ORBITA' => array(
				'data_type' => 'string',
			)
		);
	}
    
    /**
     * Clear table
     */
    public static function deleteAll()
    {
        global $DB;
        $DB->Query("DELETE FROM ".self::getTableName(), false);
        $DB->Query("ALTER TABLE ".self::getTableName()." AUTO_INCREMENT=1", false);
    }
}