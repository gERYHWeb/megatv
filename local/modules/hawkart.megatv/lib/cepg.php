<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CEpg
{
    private static $ftpUrl = 'xmldata.epgservice.ru';
    private static $ftpLogin = 'saturn';
    private static $ftpPasss = '7M3Z17cp';
    private static $ftpFile = 'TV_Pack.xml';
    protected static $origin_dir = "/upload/epg_original/";
    protected static $cut_dir = "/upload/epg_cut/";
    
    public function __construct($dir = false)
    {
        if(!$dir)
            $dir = $_SERVER["DOCUMENT_ROOT"]."/upload/";
            
        $this->dir = $dir;
        $this->file = $this->dir.self::$ftpFile;
        
        $file = 'http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturnapi/index';
        $this->xml = simplexml_load_file($file);
    }
    
    public function importChannelCity()
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
        
        foreach($this->xml->channel as $_arChannel)
        {
            $attr = $_arChannel->attributes();
            $channel_epg_id = trim((string)$attr["id"]);
            
            foreach($_arChannel->{'broadcast-city'}->city as $_arCity)
            {
                $json = json_encode($_arCity);
                $arCity = json_decode($json, TRUE);
                $city = trim((string)$arCity[0]);
                
                $city_id = $arCities[$city];
                $channel_id = $arChannels[$channel_epg_id];
                
                if(intval($arChannelCity[$channel_id."-".$city_id])==0)
                {
                    $arFields = array(
                        "UF_CITY_ID" => $city_id,
                        "UF_CHANNEL_ID" => $channel_id
                    );
                    $result = ChannelCityTable::add($arFields);
                    if ($result->isSuccess())
                    {
                        $id = $result->getId();
                        $arChannelCity[$channel_id."-".$city_id] = $id;
                    }
                }
            }
        }
    }
    
    public static function exportChannelCity()
    {
        $arRows = array();
        
        $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/upload/channel_city.csv", 'w');
        $firstStr = array("Субъект федерации"."\t", "Город/Канал"."\t");
        
        $arChannelCity = array();
        $arSelect = array(
            "ID", "UF_CHANNEL_ID", "UF_CITY_ID",
            "UF_BASE_ID" => "UF_CHANNEL.UF_BASE_ID", 
            "UF_EPG_ID" => "UF_CHANNEL.UF_EPG_ID"
        );
        $result = ChannelCityTable::getList(array(
            'filter' => array(),
            'select' => $arSelect
        ));
        while ($arItem = $result->fetch())
        {
            $arChannelCity[$arItem["UF_CITY_ID"]][$arItem["UF_BASE_ID"]] = $arItem["UF_EPG_ID"];
        }
        
        $arCities = array();
        $arFilter = array(
            "UF_COUNTRY.UF_TITLE" => "Россия", 
            "UF_ACTIVE" => 1
        );
        $arSelect = array("ID", "UF_TITLE", "UF_REGION");
        $result = CityTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect,
            'order' => array("UF_TITLE" => "ASC")
        ));
        while ($arCity = $result->fetch())
        {
            $arCities[$arCity["ID"]] = $arCity;
        }
        
        $arChannels = array();
        $arFilter = array("UF_ACTIVE" => 1);
        $arSelect = array("ID", "UF_TITLE");
        $result = ChannelBaseTable::getList(array(
            'filter' => $arFilter,
            'select' => $arSelect
        ));
        while ($arChannel = $result->fetch())
        {
            $arChannels[$arChannel["ID"]] = $arChannel["UF_TITLE"];
            $firstStr[] = $arChannel["UF_TITLE"]."\t";
        }
        
        $arRows[] = $firstStr;

        fputcsv($fp, $firstStr, ";");
        
        foreach($arCities as $city_id => $arCity)
        {
            $arStr = array($arCity["UF_REGION"], $arCity["UF_TITLE"]);
            
            foreach($arChannels as $channel_id => $channel_title)
            {
                $arStr[] = (string)$arChannelCity[$city_id][$channel_id]."\t";
            }
            
            $arRows[] = $arStr;
            
            fputcsv($fp, $arStr);
        }
        
        fclose($fp);
        
        $fileName = 'channel_city.csv';
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename={$fileName}");
        header("Expires: 0");
        header("Pragma: public");
        $fh = @fopen( 'php://output', 'w' );
        foreach ( $arRows as $data ) 
        {         
            fputcsv($fh, $data, ";");
        }
        fclose($fh);
        exit;
    }
    
    /**
	 * Download file xml from EPG service to server
	 *
	 */
    public function importChannels()
    {   
        $arHrefChannels = array();
        $arBaseChannels = array();
        $result = ChannelBaseTable::getList(array(
            'filter' => array(),
            'select' => array("UF_EPG_ID", "ID", "UF_ACTIVE"),
            'order' => array("ID" => "ASC")
        ));
        while ($row = $result->fetch())
        {
            if($arBaseChannels[$row["UF_EPG_ID"]]["ID"]>0)
            {
                ChannelBaseTable::delete($row["ID"]);   //if dublicate exist
            }else{
                $arBaseChannels[$row["UF_EPG_ID"]] = $row;
            }
        }
        
        $arChannels = array();
        $result = ChannelTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_BASE_ID")
        ));
        while ($row = $result->fetch())
        {
            $arChannels[$row["UF_EPG_ID"]] = $row;
        }
        
        foreach($this->xml->channel as $_arChannel)
        {
            $attr = $_arChannel->{'base-channel'}->attributes();
            $base_epg_id = trim((string)$attr["id"]);
            
            $json = json_encode($_arChannel);
            $arChannel = json_decode($json, TRUE);

            $epg_id = trim((string)$arChannel["@attributes"]["id"]);
            $name = trim((string)$arChannel["display-name"]);
            $base_title = trim((string)$arChannel["base-channel"]);
            $icon = (string)$arChannel["@attributes"]["src"];
            $href = trim((string)$arChannel["href"]);
            
            $arHrefChannels[$epg_id][] = $href;
            
            if(!empty($base_epg_id) && $base_epg_id!=$epg_id)
            {
                ChannelBaseTable::delete($epg_id);
                unset($arBaseChannels[$epg_id]);
                
                if(!is_array($arBaseChannels[$base_epg_id]))
                {
                    $arFields = array(
                        "UF_ACTIVE" => 0,
                        "UF_EPG_ID" => $base_epg_id,
                        "UF_TITLE" => trim((string)$arChannel["base-channel"])
                    );
                    
                    $result = ChannelBaseTable::add($arFields);
                    if ($result->isSuccess())
                    {
                        $id = $result->getId();
                        $arFields["ID"] = $id;
                        $arBaseChannels[$base_epg_id] = $arFields;
                    }else{
                        $errors = $result->getErrorMessages();
                    }
                }
                
            }else{
                if(!is_array($arBaseChannels[$epg_id]))
                {
                    $arFields = array(
                        "UF_ACTIVE" => 0,
                        "UF_EPG_ID" => $epg_id,
                        "UF_TITLE" => $name
                    );
                    $result = ChannelBaseTable::add($arFields);
                    if ($result->isSuccess())
                    {
                        $id = $result->getId();
                        $arFields["ID"] = $id;
                        $arBaseChannels[$epg_id] = $arFields;
                    }else{
                        $errors = $result->getErrorMessages();
                    }
                }
            }

            if(!is_array($arChannels[$epg_id]))
            {
                if(empty($base_epg_id))
                    $base_epg_id = $epg_id;
                
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_BASE_ID" => $arBaseChannels[$base_epg_id]["ID"]
                );
                $result = ChannelTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arChannels[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
            
            if(empty($base_epg_id)) $base_epg_id = $epg_id;    
            $arChannels[$epg_id]["UF_BASE_EPG_ID"] = $base_epg_id;
        }
        
        $this->base_channels = $arBaseChannels;
        $this->channels = $arChannels;
        $this->href_channels = $arHrefChannels;
        
        return $arChannels;
    }
    
    /**
	 * Import categories from xml EPG
	 *
     * @return array
	 */
    public static function importCategory()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/category_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = CategoryTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];
            
            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title
                );
                $result = CategoryTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages(); //print_r($errors);
                }
            }
            //\CDev::pre($arElement);
        }
        
        return $arList;
    }
    
    /**
	 * Import ganres from xml EPG
	 *
     * @return array
	 */
    public static function importGanre()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/ganre_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = GanreTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];
            
            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title
                );
                $result = GanreTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
        }
        
        return $arList;
    }
    
    /**
	 * Import topics from xml EPG
	 *
     * @return array
	 */
    public static function importTopic()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/topic_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = TopicTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];
            
            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title
                );
                $result = TopicTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
        }
        
        return $arCategories;
    }
    
    /**
	 * Import countries from xml EPG
	 *
     * @return array
	 */
    public static function importCountry()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/country_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = CountryTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];
            $iso = (string)$arElement["@attributes"]["ISO"];
            
            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title,
                    "UF_ISO" => $iso,
                    "UF_ACTIVE" => 1,
                    "UF_EXIST" => 1,
                );
                $result = CountryTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
        }
        
        return $arCategories;
    }
    
    /**
	 * Import Productions from xml EPG
	 *
     * @return array
	 */
    public static function importProduction()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/production_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = ProductionTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];

            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title
                );
                $result = ProductionTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
        }
        
        return $arList;
    }
    
    /**
	 * Import Roles from xml EPG
	 *
     * @return array
	 */
    public static function importRole()
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/role_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = RoleTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
                
        foreach($xml->element as $arElement)
        {
            $json = json_encode($arElement);
            $arElement = json_decode($json, TRUE);
            
            $epg_id = (string)$arElement["@attributes"]["id"];
            $title = (string)$arElement["name"];

            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title
                );
                $result = RoleTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                }
            }
        }
        
        return $arList;
    }
    
    /**
	 * Import People from xml EPG
	 *
     * @return array
	 */
    public static function importPeople($arRoles = false)
    {
        $file = "http://xmldata.epgservice.ru/EPGService/hs/xmldata/saturn/people_list";
        $xml = simplexml_load_file($file);
        
        $arList = array();
        $result = PeopleTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arList[$row["UF_EPG_ID"]] = $row;
        }
        
        if(!$arRoles)
            $arRoles = self::importRole();
                
        foreach($xml->element as $arElement)
        {
            $el_attr = $arElement->attributes();
            $epg_id = (string)$el_attr["id"];
            $title = (string)$arElement->name;
    
            $role_attr = $arElement->role->attributes();
            $role_epg_id = (string)$role_attr["id"];
            $role_id = $arRoles[$role_epg_id]["ID"];

            if(!is_array($arList[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $title,
                    "UF_ROLE_ID" => $role_id
                );
                $result = PeopleTable::add($arFields);
                if ($result->isSuccess())
                {
                    $id = $result->getId();
                    $arFields["ID"] = $id;
                    $arList[$epg_id] = $arFields;
                }else{
                    $errors = $result->getErrorMessages();
                    //\CDev::pre($arFields);\CDev::pre($errors);
                }
            }
        }
        
        unset($arRoles);
        
        return $arList;
    }
    
    /**
     * Adding rand original image to server directory  for progs
     * 
     * @return array|boolean
     */
    public static function addImage($icons)
    {        
        $max = 0;
        $url = false;
        if(count($icons)>1)
        {
            while(!$url)
            {
                $rand_key = array_rand($icons, 1);
                $icon = $icons[$rand_key];
                if(intval($icon["@attributes"]["width"])>$width)
                {
                    $url = $icon["@attributes"]["src"];
                }                
            }
        }else{
            $url = $icons["@attributes"]["src"];
        }
        
        if($url)
        {
            $path_parts = pathinfo($url);
            $file_name = $path_parts["filename"];
            $path = self::$origin_dir. $file_name. ".jpg";
            file_put_contents($_SERVER["DOCUMENT_ROOT"]. $path, file_get_contents($url));
        }else{
            $file_name = "default";
            $path = self::$origin_dir. "default.jpg";
            
            return false;          
        }
        
        list($width, $height, $type, $attr) = getimagesize($_SERVER["DOCUMENT_ROOT"]. $path);

        return array(
            "origin_path" => $url,
            "server_path" => $path,
            "width" => $width,
            "height" => $height
        );
    }
    
    /**
     * Make cut images for imported progs 
     */
    public static function addCropFiles($prog_ids)
    {
        $arProgs = array();
        $result = ProgTable::getList(array(
            'filter' => array("=ID" => $prog_ids, ">UF_IMG_ID" => 0),
            'select' => array('UF_IMG_PATH' => "UF_IMG.UF_PATH")
        ));
        while ($row = $result->fetch())
        {
            $path_from = $row["UF_IMG_PATH"];
            $path_parts = pathinfo($row["UF_IMG_PATH"]);
            $file_name = $path_parts["filename"];
            
            $arCropedSize = array(
                array(288, 144),
                array(288, 288),
                array(576, 288),
                array(300, 300),
                array(600, 600)/*,
                array(300, 550),*/
            );
            foreach($arCropedSize as $arSize)
            {
                $path_to = self::$cut_dir. $file_name. "_". $arSize[0]. "_". $arSize[1]. ".jpg";
                
                CFile::add(array(
                    "path_from" => $_SERVER["DOCUMENT_ROOT"]. $path_from,
                    "path_to" => $_SERVER["DOCUMENT_ROOT"]. $path_to,
                    "width" => $arSize[0],
                    "height" =>  $arSize[1]
                ));
            }
        }
    }
    
    
    /**
     * Import Epg file 
     */
    public function import()
    {
        $arProgCropIds = array();   //prog id list to crop image
        $arScheduleIdsNotDelete = array();
        $arProgTimeDelete = array();            //Расписание, которое нужно удалить
        //$arCategories = self::importCategory();
        //$arGanres = self::importGanre();
        //$arTopics = self::importTopic();
        //$arCountries = self::importCountry();
        //$arProductions = self::importProduction();
        //$arRoles = self::importRole();
        //$arPeople = self::importPeople($arRoles);
        $arBaseChannels = $this->base_channels;
        $arChannels = $this->channels;
        
        //Delete cropped images
        //\CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. self::$cut_dir, 0);
        
        //Delete schedule before prev day
        ScheduleTable::deleteOld();
        
        /**
         * Get prog's list
         */
        $arProgs = array();
        $result = ProgTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "UF_EPG_SUB_ID", "ID", "UF_IMG_ID")
        ));
        while ($row = $result->fetch())
        {
            $arProgs[$row["UF_EPG_ID"].$row["UF_EPG_SUB_ID"]] = $row;
        }
        
        /**
         * Get schedule's list
         */
        $arSchedules = array();
        $result = ScheduleTable::getList(array(
            'filter' => array(),
            'select' => array("UF_EPG_ID", "ID")
        ));
        while ($row = $result->fetch())
        {
            $arSchedules[$row["UF_EPG_ID"]] = $row["ID"];
        }
		
		//die("aaaaaaaaaaaaaaaaa");
        
        foreach($this->href_channels as $channel_epg_id => $xml_urls)
        {
            $arChannel = $arChannels[$channel_epg_id];
            $active = intval($arBaseChannels[$arChannel["UF_BASE_EPG_ID"]]["UF_ACTIVE"]);
            
            if(intval($arChannel["ID"])<=0 || empty($arChannel["ID"])  || $active!=1 || empty($channel_epg_id))
                continue;
            
            foreach($xml_urls as $xml_url)
            {
                $xml = simplexml_load_file($xml_url);

                //$attr = $xml->channel->attributes();
                //$channel_epg_id = (string)$attr["id"];
                
                /**
                 * Update Progs
                 */
                foreach($xml->programme as $arProg)
                {
                    $_arProg = $arProg;
                    $json = json_encode($arProg);
                    $arProg = json_decode($json, TRUE);
                    
                    $arFields = array(
                        "UF_ACTIVE" => 1,
                        "UF_TITLE" => trim((string)$arProg["title"]),
                        "UF_DESC" => (string)$_arProg->desc,
                        "UF_YEAR_LIMIT" => (int)$arProg["rating"]["value"],
                        "UF_YEAR" => (string)$arProg["year"]
                    );
                    
                    if(isset($arProg["title"]))
                    {
                        $attr = $_arProg->title->attributes();
                        $arFields["UF_EPG_ID"] = (string)$attr["id"];
                        $prog_epg_id = (string)$attr["id"];
                    }
 
                    if(isset($arProg["sub-title"]) && !empty($arProg["sub-title"]))
                    {
                        $arFields["UF_SUB_TITLE"] = trim($arProg["sub-title"]);
                        $attr = $_arProg->{'sub-title'}->attributes();
                        $arFields["UF_EPG_SUB_ID"] = (string)$attr["id"];
                        $prog_epg_id.= $arFields["UF_EPG_SUB_ID"];
                    }else{
                        
                        //get sub_epg_id from full id if no sub-title
                        $full_epg_id = (string) $arProg["@attributes"]["id"];
                        $pos = strripos($full_epg_id, $prog_epg_id) + strlen($prog_epg_id);
    
                        if($pos!=strlen($full_epg_id))
                        {
                            $epg_sub_id = substr($full_epg_id, $pos);
                            if(!empty($epg_sub_id))
                            {
                                $arFields["UF_EPG_SUB_ID"] = $epg_sub_id;
                                $prog_epg_id.= $arFields["UF_EPG_SUB_ID"];
                            }
                        } 
                    }
                                   
                    if (!array_key_exists($prog_epg_id, $arProgs))
                    {
                        if(!empty($_arProg->{"desc-sub-title"}))
                        {
                            $arFields["UF_SUB_DESC"] = (string) $_arProg->{"desc-sub-title"};
                        }
                        
                        if(!empty($_arProg->{"episode-number"}))
                            $arFields["UF_SERIA"] = (string) $_arProg->{"episode-number"};
                        
                        if(!empty($_arProg->season))
                            $arFields["UF_SEASON"] = (string) $_arProg->season;
                        
                        if(!is_array($arProg["category"]))
                            $arProg["category"] = array($arProg["category"]);
                        $arFields["UF_CATEGORY"] = implode(", ", $arProg["category"]);
                        
                        if(!is_array($arProg["topic"]))
                            $arProg["topic"] = array($arProg["topic"]);
                        $arFields["UF_TOPIC"] = implode(", ", $arProg["topic"]);
                        
                        if(!is_array($arProg["ganre"]))
                            $arProg["ganre"] = array($arProg["ganre"]);
                        $arFields["UF_GANRE"] = implode(", ", $arProg["ganre"]);
                        
                        if(!is_array($arProg["country"]))
                            $arProg["country"] = array($arProg["country"]);
                        $arFields["UF_COUNTRY"] = implode(", ", $arProg["country"]);
                
                        if(!is_array($arProg["credits"]["director"]))
                            $arProg["credits"]["director"] = array($arProg["credits"]["director"]);
                        $arFields["UF_DIRECTOR"] = implode(", ", $arProg["credits"]["director"]);
                        
                        if(!is_array($arProg["credits"]["actor"]))
                            $arProg["credits"]["actor"] = array($arProg["credits"]["actor"]);
                        $arFields["UF_ACTOR"] = implode(", ", $arProg["credits"]["actor"]);
                        
                        if(!is_array($arProg["credits"]["presenter"]))
                            $arProg["credits"]["presenter"] = array($arProg["credits"]["presenter"]);
                        $arFields["UF_PRESENTER"] = implode(", ", $arProg["credits"]["presenter"]);
                        
                        
                        /**
                         * Bugfix: Problem with sub-title id.
                         * If no sub-title id, but it's serial, update previous prog
                         */
                        if(array_key_exists($arFields["UF_EPG_ID"], $arProgs) && $prog_epg_id!=$arFields["UF_EPG_ID"])
                        {
                            $arProgs[$prog_epg_id] = $arProgs[$arFields["UF_EPG_ID"]];
                            unset($arProgs[$arFields["UF_EPG_ID"]]);
                            $prog_id = $arProgs[$prog_epg_id]["ID"];
                            
                            ProgTable::Update($prog_id, $arFields);
                        }else{
                            
                            $result = ProgTable::add($arFields);
                            if ($result->isSuccess())
                            {
                                $prog_id = $result->getId();
                                $arProgs[$prog_epg_id] = array(
                                    "ID" => $prog_id,
                                    "UF_IMG_ID" => 0
                                );
                            }else{
                                $errors = $result->getErrorMessages();
                            }
                        }
                        
                    }else{
                        $prog_id = $arProgs[$prog_epg_id]["ID"];
                    }
                    
                    //--------------Add original image----------------
                    if(intval($arProgs[$prog_epg_id]["UF_IMG_ID"])==0)
                    {
                        $arFields = array();
                        $icons = array();
                        if(!is_array($arProg["icon"]))
                            $arProg["icon"] = array($arProg["icon"]);
                        
                        $ar_src = self::addImage($arProg["icon"]);
                        if($ar_src)
                        {
                            $result = ImageTable::getList(array(
                                'filter' => array("=UF_EXTERNAL_ID" => md5($ar_src["origin_path"])),
                                'select' => array("ID")
                            ));
                            if ($row = $result->fetch())
                            {
                                $arFields["UF_IMG_ID"] = (int)$row["ID"];
                            }else{
                                
                                $resultImg = ImageTable::add(array(
                                    "UF_PATH" => $ar_src["server_path"],
                                    "UF_EXTERNAL_ID" => md5($ar_src["origin_path"]),
                                    "UF_WIDTH" => intval($ar_src["width"]),
                                    "UF_HEIGHT" => intval($ar_src["height"])
                                ));
                                if ($resultImg->isSuccess())
                                {
                                    $arFields["UF_IMG_ID"] = (int)$resultImg->getId();
                                }
                            }
                        }
    
                        ProgTable::Update($prog_id, $arFields);
                    }
                    //------------------------------------------------
                    
                    
                    /**
                     * Adding schedules
                     */
                    $schedule_epg_id = trim((string) $arProg["@attributes"]["id"]);
                    
                    $dateStart = new \Bitrix\Main\Type\DateTime( date("Y-m-d H:i:s", strtotime($arProg["@attributes"]["start"])), 'Y-m-d H:i:s' );
                    $dateEnd = new \Bitrix\Main\Type\DateTime( date("Y-m-d H:i:s", strtotime($arProg["@attributes"]["stop"])), 'Y-m-d H:i:s' );
                    $date = new \Bitrix\Main\Type\Date(date("Y-m-d", strtotime($arProg["date"])), 'Y-m-d');
                    
                    if (!array_key_exists($schedule_epg_id, $arSchedules))
                    {
                        $title = (string)$arProg["title"];
                        if(!empty($arProg["sub-title"]))
                        {
                            $title.= " | ". trim($arProg["sub-title"]);
                        }
                        
                        $arFields = array(
                            "UF_ACTIVE" => 1,
                            "UF_DATE_START" => $dateStart,
                            "UF_DATE_END" => $dateEnd,
                            "UF_DATE" => $date,
                            "UF_CHANNEL_ID" => (int)$arChannel["ID"],
                            "UF_PROG_ID" => (int)$prog_id,
                            "UF_EPG_ID" => $schedule_epg_id,
                            "UF_CODE" => $title." - ". $arProg["@attributes"]["start"]
                        );
                        
                        $result = ScheduleTable::add($arFields);
                        if ($result->isSuccess())
                        {
                            $schedule_id = $result->getId();
                            $arSchedules[$schedule_epg_id] = $schedule_id;
                        }else{
                            $errors = $result->getErrorMessages();
                        }
                    }else{
                        $schedule_id = (int) $arSchedules[$schedule_epg_id];
                        
                        $arFields = array(
                            "UF_DATE_START" => $dateStart,
                            "UF_DATE_END" => $dateEnd,
                            "UF_DATE" => $date,
                        );
                        
                        ScheduleTable::Update($schedule_id, $arFields);
                    }
                    $arScheduleIdsNotDelete[] = $schedule_id;
                    
                    $arProgCropIds[] = $prog_id;
                    
                }
            }
        }
        
        /**
         * Delete not exist schedule
         */
        if(!empty($arScheduleIdsNotDelete))
        {
            $result = ScheduleTable::getList(array(
                'filter' => array(
                    ">=UF_DATE" => new \Bitrix\Main\Type\Date(date("Y-m-d"), 'Y-m-d')
                ),
                'select' => array("ID")
            ));
            while ($row = $result->fetch())
            {
                if(!in_array($row["ID"], $arScheduleIdsNotDelete))
                {
                    ScheduleTable::delete($row["ID"]);
                }
            }
        }
        
        //Make cut images for imported progs 
        self::addCropFiles($arProgCropIds);
        
        unset($arScheduleIdsNotDelete);
        unset($arProgCropIds);
        
        self::importSerials();
    }
    
    public static function importSerials()
    {
        /**
         * Get serials list
         */
        $arSerials = array();
        $result = SerialTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_CHANNEL_ID")
        ));
        while ($row = $result->fetch())
        {
            $arSerials[$row["UF_EPG_ID"]] = $row;
        }
        
        /**
         * Get prog's list
         */
        $arProgs = array();
        $result = ProgTable::getList(array(
            'filter' => array(),
            'select' => array("UF_EPG_SUB_ID", "ID", "UF_TITLE", "UF_EPG_ID")
        ));
        while ($row = $result->fetch())
        {
            if(!empty($row["UF_EPG_SUB_ID"]))
                $arProgs[$row["UF_EPG_ID"]] = $row["UF_TITLE"];
        }
        
        foreach($arProgs as $epg_id => $serial_title)
        {
            if(empty($arSerials[$epg_id]))
            {
                $arFields = array(
                    "UF_EPG_ID" => $epg_id,
                    "UF_TITLE" => $serial_title,
                );
                
                $result = SerialTable::add($arFields);
                if ($result->isSuccess())
                {
                    $serial_id = $result->getId();
                    $arSerials[$epg_id] = array(
                        "ID" => $serial_id
                    );
                }
            }
        }
    }
    
    /**
     * Clear tables & deleting images
     */
    public static function clear()
    {
        ProgTable::deleteAll();
        ScheduleTable::deleteAll();
        //ChannelTable::deleteAll();
        ImageTable::deleteAll();
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. self::$cut_dir, 0);
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. self::$origin_dir, 0);
    }
} 