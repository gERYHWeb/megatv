<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CityTable extends Entity\DataManager
{
    public static $cacheDir = "city";
    public static $defaultCityID = 2;
        
    public static function getTimezoneByCity()
    {
        $arItems = array();
        $arFilter = array(
            "UF_COUNTRY.UF_TITLE" => "Россия", 
            "UF_ACTIVE" => 1
        );
        $arSelect = array("ID", "UF_TITLE");
        $result = CityTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect,
            'order' => array("UF_TITLE" => "ASC")
        ));
        while ($arCity = $result->fetch())
        {
            $arItems[$arCity["UF_TITLE"]] = $arCity["ID"];
        }
        
        $arIps = GeoCity::getInstance()->getIpByCities($arItems);
        
        foreach($arIps as $city_id => $range_ip)
        {
            $ips = explode("-", $range_ip);
            $ip = trim($ips[0]);
            
            $json = file_get_contents("http://api.sypexgeo.net/json/".$ip);
            $json = json_decode($json, true);
            
            $utc = $json["region"]["utc"];
            
            $arFields = array(
                "UF_TIMEZONE" => $utc
            );
            CityTable::update($city_id, $arFields);
        }
    }
    
    /**
     * import russian capital cities
     */
    public static function importCapitalCity()
    {
        $arItems = array();
        
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
            $arItems[$arCity["UF_TITLE"]] = $arCity["ID"];
        }
        
        $file = $_SERVER["DOCUMENT_ROOT"]."/local/modules/hawkart.megatv/data/capital_cities.txt";
        $lines = file($file);
        foreach ($lines as $line_num => $line) 
        {
            $pos = strripos($line, ".svg ");
            if ($pos === false) $pos = strripos($line, ".png ");  
            if ($pos === false) $pos = strripos($line, ".jpg "); 
            
            $city = substr($line, $pos+5);
            $city = trim($city);
            
            $pos = strripos($line, "Flag");
            $region = substr($line, 0, $pos);
            $region = trim($region);
            
            if(intval($arItems[$city]["ID"])==0)
            {
                $arFields = array(
                    "UF_TITLE" => $city,
                    "UF_REGION" => $region,
                    "UF_ACTIVE" => 1,
                    "UF_COUNTRY_ID" => 15
                );
                $result = CityTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arItems[$city] = $id;
                }
            }
        }
    }
    
    /**
     * Get city by Geo
     * 
     * @return array
     */
    public static function getGeoCity()
    {
        global $currentGeo;
        $arSelect = array("ID", "UF_TITLE", "UF_TIMEZONE");
        
        //unset($_SESSION["USER_GEO"]);
        //unset($_COOKIE["city_select_data"]);
        
        //should be deleted after
        //$result = self::getById(self::$defaultCityID);
        //$_SESSION["USER_GEO"] = $result->fetch();
        
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
                $arGeo = GeoCity::getInstance()->getRecord();
                
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
            'UF_TIMEZONE' => array(
				'data_type' => 'string',
			),
		);
	}
}