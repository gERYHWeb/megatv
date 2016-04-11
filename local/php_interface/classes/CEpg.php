<?
class CEpg
{
    private static $ftpUrl = 'xmldata.epgservice.ru';
    private static $ftpLogin = 'saturn';
    private static $ftpPasss = '7M3Z17cp';
    private static $ftpFile = 'TV_Pack.xml';
    
    public function __construct($dir = false)
    {
        if(!$dir)
            $dir = FULL_PATH_DOCUMENT_ROOT."/upload/";
            
        $this->dir = $dir;
        $this->file = $this->dir.self::$ftpFile;
    }
    
    public function download()
    {
        // установка соединения
        $conn = ftp_connect(self::$ftpUrl);
        
        // вход с именем пользователя и паролем
        $login_result = ftp_login($conn, self::$ftpLogin, self::$ftpPasss);
        
        if (ftp_get($conn, $this->file , self::$ftpFile, FTP_BINARY)) 
        {
            echo "Downloaded $this->file \r\n<br />";
        } else {
            echo "Error downloaded\r\n<br />";
        }
        
        // закрытие соединения
        ftp_close($conn);
    }
    
    public static function resizeImage($icons, $type)
    {
        switch($type)
        {
            case "preview":  //large
                $width = 600;
                $height = 600;
            break;
            case "horizontal":  //medium
                $width = 288;
                $height = 144;
            break;
            case "horizontal_double":  //small
                $width = 576;
                $height = 288;
            break;
            case "vertical":  //large
                $width = 300;
                $height = 500;
            break;
            case "vertical_double":  //medium
                $width = 600;
                $height = 550;
            break;
        }
        
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
                    
            /*foreach($icons as $icon)
            {
                if(intval($icon["@attributes"]["width"])>$max)
                {
                    $max = intval($icon["@attributes"]["width"]);
                    $url = $icon["@attributes"]["src"];
                }
            }*/

        }else{
            $url = $icons["@attributes"]["src"];
        }
        
        
        if($url)
        {
            $path_parts = pathinfo($url);
            $file_name = $path_parts["filename"];
            $path = FULL_PATH_DOCUMENT_ROOT."/upload/tmp/".$file_name.".jpg";
            $path_cut = FULL_PATH_DOCUMENT_ROOT."/upload/epg/".$file_name."_cut_".$width."_".$height.".jpg";
        }else{
            $path = $default = FULL_PATH_DOCUMENT_ROOT."/upload/default.jpg";
            $path_cut = FULL_PATH_DOCUMENT_ROOT."/upload/epg/default_cut_".$width."_".$height.".jpg";
            
            return false;
        }
        
        file_put_contents($path, file_get_contents($url));
        
        $resizeRez = CFile::ResizeImageFile( // уменьшение картинки для превью
            $path,
            $dest = $path_cut,
            array(
                'width' => $width,
                'height' => $height,
            ),
            $resizeType = BX_RESIZE_IMAGE_EXACT,//BX_RESIZE_IMAGE_PROPORTIONAL, // метод ресайза
            $waterMark = array(), // водяной знак (пустой)
            $jpgQuality = 100 // качество уменьшенной картинки в процентах
        );
        
        if($path != $default)
            unlink($path);
        
    	return $path_cut;
    }
    
    public function import()
    {
        if (file_exists($this->file))
            $xml = simplexml_load_file($this->file);
        
        $log_file = "/logs/import_".date("d_m_Y_H_i").".txt";
        
        $epgUrl = CXmlEx::getAttr($xml, "generator-info-url");
        $epgName = CXmlEx::getAttr($xml, "generator-info-name");
        
        $arScheduleIdsNotDelete = array();
        
        //Расписание, которое нужно удалить
        $arProgTimeDelete = array();
        
        //получим все каналы из кэша
        CChannel::updateCache();
        $arChannels = CChannel::getList(false, array("ID", "PROPERTY_EPG_ID"));
        
        foreach($xml->channel as $arChannel)
        {
            $id = (string)CXmlEx::getAttr($arChannel, "id");
            $name = (string)$arChannel->{'display-name'};
            
            $icon = (string)CXmlEx::getAttr($arChannel->icon, "src");
            $icon = "http://". $icon;
            
            if(!isset($arChannels[$id]))
            {
                $arFields = array(
                    "EPG_ID" => $id,
                    "NAME" => $name,
                    "ICON_SRC" => $icon
                );
                $newID = CChannel::add($arFields);
                if(intval($newID)==0)
                {
                    echo $newID."\r\n<br />";
                }else{
                    echo "Added channel  ".$newID."\r\n<br />";
                }
            }
        }
        
        //сбросим и обновим кэш после загрузки
        CChannel::updateCache();
        $arChannels = CChannel::getList(false, array("ID", "PROPERTY_EPG_ID", "ACTIVE"));

        //список программ и время вещания из кэша
        CProg::updateCache();
        CProgTime::updateCache();        
        $arProgs = CProg::getList(false, array(
            "ID", "NAME", "PREVIEW_TEXT", "PROPERTY_CHANNEL", "PROPERTY_SUB_TITLE", "PREVIEW_PICTURE",
            "PROPERTY_PICTURE_DOUBLE", "PROPERTY_PICTURE_HALF", "PROPERTY_PICTURE_VERTICAL", "PROPERTY_PICTURE_VERTICAL_DOUBLE"
        ));
        $arProgTimes = CProgTime::getList(false, array("ID", "PROPERTY_CHANNEL", "PROPERTY_DATE_START"));
        
        foreach($xml->programme as $arProg)
        {
            $_arProg = $arProg;
            $json = json_encode($arProg);
            $arProg = json_decode($json, TRUE);
            
            $arChannel = $arChannels[$arProg["@attributes"]["channel"]];
            
            if(intval($arChannel["ID"])==0 || $arChannel["ACTIVE"]=="N")
                continue;
            
            $arFields = array(
                "FIELDS" => array(
                    "NAME" => trim($arProg["title"]),
                    "PREVIEW_TEXT" => $arProg["desc"],
                    "DETAIL_TEXT" => $arProg["desc"],
                ),
                "PROPS" => array(
                    "CHANNEL" => $arChannel["ID"],
                    "YEAR_LIMIT" => $arProg["rating"]["value"],
                    "YEAR" => $arProg["year"]
                )
            );
            
            if(isset($arProg["title"]))
            {
                $attr = $_arProg->title->attributes();
                $arFields["UF_EPG_ID"] = (string)$attr["id"];
                $epg_id = (string)$attr["id"];
            }
            
            if(isset($arProg["sub-title"]))
            {
                $arFields["PROPS"]["SUB_TITLE"] = trim($arProg["sub-title"]);
                $attr = $_arProg->{'sub-title'}->attributes();
                $arFields["PROPS"]["EPG_SUB_ID"] = (int)$attr["id"];
            }
            
            //генерируем идентификатор программы для проверки на существование
            if(!empty($arFields["PROPS"]["SUB_TITLE"]))
            {
                $progName = $arFields["FIELDS"]["NAME"]." (".trim($arFields["PROPS"]["SUB_TITLE"]).")";
            }else{
                $progName = $arFields["FIELDS"]["NAME"];
            }
            
            if(!is_array($arProg["credits"]["actor"]))
                $arProg["credits"]["actor"] = array($arProg["credits"]["actor"]);
            $arFields["PROPS"]["ACTOR"] = implode(", ", $arProg["credits"]["actor"]);
            
            if(!is_array($arProg["credits"]["presenter"]))
                $arProg["credits"]["presenter"] = array($arProg["credits"]["presenter"]);
            $arFields["PROPS"]["PRESENTER"] = implode(", ", $arProg["credits"]["presenter"]);
            
            $unique = CProg::generateUnique(array(
                "CHANNEL" => intval($arFields["PROPS"]["CHANNEL"]),
                "NAME" => $progName,
                "DESC" => $arFields["FIELDS"]["PREVIEW_TEXT"],
                "ACTOR" => $arFields["PROPS"]["ACTOR"],
                "PRESENTER" => $arFields["PROPS"]["PRESENTER"],
            ));
            
            if (!array_key_exists($unique, $arProgs))
            {
                if(!empty($arProg['episode-number']))
                {
                    $arFields["PROPS"]["SERIA"] = intval($arProg['episode-number']);
                }
                if(!empty($arProg['season']))
                {
                    $arFields["PROPS"]["SEASON"] = intval($arProg['season']);
                }
                
                if(!is_array($arProg["category"]))
                    $arProg["category"] = array($arProg["category"]);
                $arFields["PROPS"]["CATEGORY"] = implode(", ", $arProg["category"]);
                
                if(!is_array($arProg["topic"]))
                    $arProg["topic"] = array($arProg["topic"]);
                $arFields["PROPS"]["TOPIC"] = implode(", ", $arProg["topic"]);
                
                if(!is_array($arProg["ganre"]))
                    $arProg["ganre"] = array($arProg["ganre"]);
                $arFields["PROPS"]["GANRE"] = implode(", ", $arProg["ganre"]);
                
                if(!is_array($arProg["country"]))
                    $arProg["country"] = array($arProg["country"]);
                $arFields["PROPS"]["COUNTRY"] = implode(", ", $arProg["country"]);

                if(!is_array($arProg["credits"]["director"]))
                    $arProg["credits"]["director"] = array($arProg["credits"]["director"]);
                $arFields["PROPS"]["DIRECTOR"] = implode(", ", $arProg["credits"]["director"]);
                
                $progID = CProg::add($arFields);
                if(intval($progID)==0)
                {
                    CDev::log(array(
                        "ERROR" => $progID,
                        "PROG" => $arFields,
                    ), false, $log_file);
                    
                    echo $progID."<br />";
                }else{
                    echo "Added prog ".$progID."<br />";
                    
                    //Добавляем в массив, чтобы дубляжа при подании одинаковой передачи
                    $arProgs[$unique] = array(
                        "ID" => $progID
                    );
                }
            }else{
                $progID = $arProgs[$unique]["ID"];
                CIBlockElement::SetPropertyValueCode($progID, "EPG_ID", $epg_id);
                $attr = $_arProg->{'sub-title'}->attributes();
                CIBlockElement::SetPropertyValueCode($progID, "EPG_SUB_ID", (int)$attr["id"]);
                
                CIBlockElement::SetPropertyValueCode($progID, "CATEGORY", $arProg["category"]);
            }
            
            if(empty($arProgs[$unique]["PREVIEW_PICTURE"]) || !empty($arProgs[$unique]["PREVIEW_PICTURE"]))
            {
                $icons = array();
                if(!is_array($arProg["icon"]))
                    $arProg["icon"] = array($arProg["icon"]);
                
                $icons = $arProg["icon"];
                
                if(!empty($arProgs[$unique]["PROPERTY_PICTURE_DOUBLE_VALUE"]))
                {
                    $arProps = array("PICTURE_DOUBLE", "PICTURE_HALF", "PICTURE_VERTICAL", "PICTURE_VERTICAL_DOUBLE");
                    foreach($arProps as $code)
                    {
                        $value = $arProgs[$unique]["PROPERTY_".$code."_VALUE_ID"];
                        CIBlockElement::SetPropertyValueCode($arProgs[$unique]["ID"], $code, Array (
                            $value =>  array('del' => 'Y', 'tmp_name' => '') 
                        ));
                        CFile::Delete($arProgs[$unique]["PROPERTY_".$code."_VALUE"]);
                    } 
                }
                
                $file = self::resizeImage($icons, "preview");
                if(!empty($file))
                {
                    $arFile = CFile::MakeFileArray($file);   
                    $arFile["MODULE_ID"] = "iblock";
                    $arFile["del"] = "Y";                 
                    $el = new CIBlockElement;
                    $el->Update($progID, array(
                        "PREVIEW_PICTURE" => $arFile,
                        "DETAIL_PICTURE" => $arFile,
                    ));
                }
                
                //$file = self::resizeImage($icons["small"], "horizontal_double");
                $file = self::resizeImage($icons, "horizontal_double");
                if(!empty($file))
                {
                    $arFile = CFile::MakeFileArray($file);
                    $arFile["MODULE_ID"] = "iblock";
                    $arFile["del"] = "Y";
                    CIBlockElement::SetPropertyValueCode($progID, "PICTURE_DOUBLE", $arFile);
                }
                
                
                //$file = self::resizeImage($icons["medium"], "horizontal");
                $file = self::resizeImage($icons, "horizontal");
                if(!empty($file))
                {
                    $arFile = CFile::MakeFileArray($file);
                    $arFile["MODULE_ID"] = "iblock";
                    $arFile["del"] = "Y";
                    CIBlockElement::SetPropertyValueCode($progID, "PICTURE_HALF", $arFile);
                }
                
                //$file = self::resizeImage($icons["large"], "vertical");
                $file = self::resizeImage($icons, "vertical");
                if(!empty($file))
                {
                    $arFile = CFile::MakeFileArray($file);
                    $arFile["MODULE_ID"] = "iblock";
                    $arFile["del"] = "Y";
                    CIBlockElement::SetPropertyValueCode($progID, "PICTURE_VERTICAL", $arFile);
                }
                
                //$file = self::resizeImage($icons["medium"], "vertical_double");
                $file = self::resizeImage($icons, "vertical_double");
                if(!empty($file))
                {
                    $arFile = CFile::MakeFileArray($file);
                    $arFile["MODULE_ID"] = "iblock";
                    $arFile["del"] = "Y";
                    CIBlockElement::SetPropertyValueCode($progID, "PICTURE_VERTICAL_DOUBLE", $arFile);
                }
            }
            
            //Добавление расписания для программы
            $dateStart = $arProg["@attributes"]["start"];
            
            $uniqueTimeID = CProgTime::generateUnique(array(
                "CHANNEL" => $arChannel["ID"],
                "DATE_START" => date("d.m.Y H:i:s", strtotime($dateStart))
            ));
            
            //Если дата меньше сегодняшне - не грузим расписание
            if( CTimeEx::dateDiff( date("d.m.Y", strtotime($arProg["date"])), date("d.m.Y") ) )
                continue;
            
            if(!isset($arProgTimes[$uniqueTimeID]) && intval($progID)>0)  
                echo $uniqueTimeID."<br />";
            
            if(!isset($arProgTimes[$uniqueTimeID]) && intval($progID)>0)
            {
                $arFields = array(
                    "FIELDS" => array(
                        "NAME" => $progName,
                    ),
                    "PROPS" => array(
                        "DATE_START" => $dateStart,
                        "DATE_END" => $arProg["@attributes"]["stop"],
                        "DATE" =>  date("d.m.Y", strtotime($arProg["date"])),
                        "CHANNEL" => $arChannel["ID"],
                        "PROG" => $progID,
                        "EPG_ID" => (int)$arProg["@attributes"]["id"]
                    )
                );
                $progTimeID = CProgTime::add($arFields);
                if(intval($progTimeID)==0)
                {
                    CDev::log(array(
                        "ERROR" => $progTimeID,
                        "PROG" => $arFields,
                    ), false, $log_file);
                    echo $progTimeID."<br />";
                }else{
                    $arProgTimes[$uniqueTimeID] = array(
                        "ID" => $progTimeID
                    );
                    echo "Added prog schedule ".$progTimeID."<br />";
                }
            }else{
                $progTimeID = $arProgTimes[$uniqueTimeID]["ID"];
                CIBlockElement::SetPropertyValueCode($progTimeID, "EPG_ID", (int)$arProg["@attributes"]["id"]);
            }
            
            $arScheduleIdsNotDelete[] = $progTimeID;
        }
        
        CProgTime::deleteNotInFile($arScheduleIdsNotDelete);
        
        unset($arProgTimes);
        unset($arScheduleIdsNotDelete);
        
        CProg::updateCache();
        CProgTime::updateCache();
    }
}