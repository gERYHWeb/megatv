<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ChannelTable extends Entity\DataManager
{
    /**
     * Get active channels by city
     * 
     * @return array
     */
    public static function getActiveByCity()
    {
        $arChannels = array();
        $arFilter = array(
            "=UF_CHANNEL.UF_BASE.UF_ACTIVE" => 1,
            "=UF_CITY_ID" => $_SESSION["USER_GEO"]["ID"]
        );
        $arSelect = array(
            'ID', 'UF_CHANNEL_ID', 'UF_CHANNEL_BASE_ID' => 'UF_CHANNEL.UF_BASE.ID', 
            'UF_TITLE' => 'UF_CHANNEL.UF_BASE.UF_TITLE', 'UF_ICON' => 'UF_CHANNEL.UF_BASE.UF_ICON',
            'UF_CODE' => 'UF_CHANNEL.UF_BASE.UF_CODE', "UF_IS_NEWS" => 'UF_CHANNEL.UF_BASE.UF_IS_NEWS'
        );
        $arSort = array("UF_CHANNEL.UF_BASE.UF_SORT" => "ASC");
        $obCache = new \CPHPCache;
        if( $obCache->InitCache(86400, serialize($arFilter).serialize($arSelect).serialize($arSort), "/channels_active/"))
        {
        	$arChannels = $obCache->GetVars();
        }
        elseif($obCache->StartDataCache())
        {
        	$result = \Hawkart\Megatv\ChannelCityTable::getList(array(
                'filter' => $arFilter,
                'select' => $arSelect,
                'order' => $arSort
            ));
            while ($row = $result->fetch())
            {
                $row["ID"] = $row["UF_CHANNEL_ID"];
                $row["DETAIL_PAGE_URL"] = "/channels/".$row['UF_CODE']."/";
                $arChannels[] = $row;
            }
        	$obCache->EndDataCache($arChannels); 
        }
        
        return $arChannels;
    }
    
    /**
     * Get active channels ids by city id
     *
     * @return array  
     */
    public static function getActiveIdByCity()
    {
        $arChannels = self::getActiveByCity();
        
        $ids = array();
        foreach($arChannels as $key=>$arChannel)
        {
            $ids[] = $arChannel["ID"];
        }
        
        unset($arChannels);
        
        return $ids;
    }
    
    /**
     * Get subscribe active channels ids by city for authorized user
     *
     * @return array  
     */
    public static function getActiveIdByCityByUser()
    {
        global $USER;
        if($USER->IsAuthorized())
        {
            $arChannels = self::getActiveByCity();
        
            $selectedChannels = array();
            $arFilter = array(
                "=UF_ACTIVE" => 1, 
                "=UF_USER_ID" => $USER->GetID(), 
                ">UF_CHANNEL_ID" => 0
            );
            $arSelect = array("UF_CHANNEL_ID");
            $result = SubscribeTable::getList(array(
                'filter' => $arFilter,
                'select' => $arSelect
            ));
            while ($arSub = $result->fetch())
            {
                $selectedChannels[] = $arSub["UF_CHANNEL_ID"];
            }
            
            $ids = array();
            foreach($arChannels as $key=>$arChannel)
            {
                if(in_array($arChannel["UF_CHANNEL_BASE_ID"], $selectedChannels))
                {
                    $ids[] = $arChannel["ID"];
                }
            }
            
            unset($selectedChannels);
            unset($arChannels);
            
            return $ids;
        }else{
            return self::getActiveIdByCity();
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
		return 'hw_channel';
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
            'UF_BASE_ID' => array(
				'data_type' => 'integer',
				'required'  => true
			),
            'UF_BASE' => array(
				'data_type' => '\Hawkart\Megatv\ChannelBaseTable',
				'reference' => array('=this.UF_BASE_ID' => 'ref.ID'),
			),
            'UF_EPG_ID' => array(
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