<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CityTable extends Entity\DataManager
{
    public static $cacheDir = "city";
    public static $defaultCityID = 2;
    
    /**
     * Get city by Geo
     * 
     * @return array
     */
    public static function getGeoCity()
    {
        global $currentGeo;
        $arSelect = array("ID", "UF_TITLE", "UF_TIMEZONE", "UF_EPG_FILE_ID");
        
        //unset($_SESSION["USER_GEO"]);
        //unset($_COOKIE["city_select_data"]);
        
        //should be deleted after
        $result = self::getById(self::$defaultCityID);
        $_SESSION["USER_GEO"] = $result->fetch();
        
        if(!$_SESSION["USER_GEO"] || empty($_SESSION["USER_GEO"]))
        {
            if($_COOKIE["city_select_data"])
            {
                $result = self::getList(array(
                    'filter' => array(
                        "=UF_TITLE" => $_COOKIE["city_select_data"],
                        "UF_ACTIVE" => 1
                    ),
                    'select' => $arSelect
                ));
                $_SESSION["USER_GEO"] = $result->fetch();
            }else{
                $arGeo = \Olegpro\IpGeoBase\IpGeoBase::getInstance()->getRecord();
                
                if(!empty($arGeo))
                {
                    $result = self::getList(array(
                        'filter' => array(
                            "=UF_REGION" => $arGeo["region"],
                            "UF_ACTIVE" => 1
                        ),
                        'select' => $arSelect
                    ));
                    $_SESSION["USER_GEO"] = $result->fetch();
                }else{
                    $result = self::getById(self::$defaultCityID);
                    $_SESSION["USER_GEO"] = $result->fetch();
                }
            }
        }
        
        $currentGeo = $_SESSION["USER_GEO"];
        
        return $_SESSION["USER_GEO"];
    }
    
    /**
     * Set cur city
     * 
     * @return array
     */
    public static function setGeoCity($ID)
    {
        global $currentGeo;
        
        if(intval($ID)==$_SESSION["USER_GEO"]["ID"])
            return $_SESSION["USER_GEO"];
            
        if(intval($ID)>0)
        {
            $result = self::getById($ID);
        }else{
            $result = self::getById(self::$defaultCityID);
        }
        
        $_SESSION["USER_GEO"] = $result->fetch();
        
        $currentGeo = $_SESSION["USER_GEO"];
        
        return $_SESSION["USER_GEO"];
    }
    
    
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_city';
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
            'UF_ACTIVE' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			),
			'UF_TITLE' => array(
				'data_type' => 'string',
                'required'  => true
			),
            'UF_REGION' => array(
				'data_type' => 'string',
                'required'  => true
			),
            'UF_COUNTRY_ID' => array(
				'data_type' => 'integer',
                'required'  => true
			),
            'UF_COUNTRY' => array(
				'data_type' => '\Hawkart\Megatv\CountryTable',
				'reference' => array('=this.UF_COUNTRY_ID' => 'ref.ID'),
			),
            'UF_EPG_FILE_ID' => array(
				'data_type' => 'integer',
                'required'  => true
			),
            'UF_EPG_FILE' => array(
				'data_type' => '\Hawkart\Megatv\EpgTable',
				'reference' => array('=this.UF_EPG_FILE_ID' => 'ref.ID'),
			),
            'UF_TIMEZONE' => array(
				'data_type' => 'string',
                'required'  => true
			),
		);
	}
}