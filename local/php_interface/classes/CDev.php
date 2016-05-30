<?
class CDev
{
    private static $firstRun = true;
    
	public static function console($data, $isAdmin = true)
    {
        global $USER;
        if(!is_object($USER)) 
            $USER = new CUser;
        
		if($isAdmin && !$USER->IsAdmin())
			return false;

		$backtrace = debug_backtrace();
		$callfrom = $backtrace[0]['file'].":".$backtrace[0]['line'];

		echo '<script>console.log("'.$callfrom.'", '.json_encode($data).');</script>';
		return true;
	}
	
	public static function pre($array, $echo = true, $isAdmin = true)
    {
        global $USER;
        if(!is_object($USER)) 
            $USER = new CUser;
        
		if($isAdmin && !$USER->IsAdmin())
			return false;
		
		$result = '<pre>'.print_r($array, true).'</pre>';
		
		if($echo)
        {
			echo $result;
			return true;
		}else{
			return $result;
		}
	}
	
	public static function var_dump($data, $echo = true, $isAdmin = true)
    {
        global $USER;
        if(!is_object($USER)) 
            $USER = new CUser;
        
		if($isAdmin && !$USER->IsAdmin())
			return false;
			
		ob_start();
		echo '<pre>'; var_dump($data); echo '</pre>';
		$result = ob_get_clean();
		
		if($echo)
			echo $result;
		else
			return $result;
	}
	
	public static function log($ar, $deleteOldData = false, $filename = LOG_FILENAME)
    {
		if(self::$firstRun && $deleteOldData)
			file_put_contents($_SERVER['DOCUMENT_ROOT'].$filename, print_r($ar, true)."\n\n");
		else
			file_put_contents($_SERVER['DOCUMENT_ROOT'].$filename, print_r($ar, true)."\n\n", FILE_APPEND);
            
		self::$firstRun = false;
	}
	
	public static function getElementById($id)
    {
        CModule::includeModule('iblock');
		if(!$obElement = CIBlockElement::getById($id)->getNextElement())
        {
			return false;
		}
		
		$element = $obElement->getFields();
		$element['PROPERTIES'] = $obElement->GetProperties();
		
		return $element;
	}
	
	public static function getElements($filter, $sort = array(), $groupBy = false, $needGetProperties = true)
    {
		CModule::includeModule('iblock');
	
		$elements = array();
		$rsElements = CIBlockElement::getList($sort, $filter, $groupBy);
		while($obElement = $rsElements->GetNextElement())
        {
			$element = $obElement->getFields();
			if(!empty($element['PREVIEW_PICTURE']))
				$element['PREVIEW_PICTURE'] = CFile::getFileArray($element['PREVIEW_PICTURE']);
				
			if(!empty($element['DETAIL_PICTURE']))
				$element['DETAIL_PICTURE'] = CFile::getFileArray($element['DETAIL_PICTURE']);
				
			if($needGetProperties)
				$element['PROPERTIES'] = $obElement->GetProperties();
				
			$elements[$element['ID']] = $element;
		}
		return $elements;
	}
	
	public static function getSections($filter, $sort = array(), $cnt = false, $select = array())
    {
		CModule::includeModule('iblock');
	
		$sections = array();
		$rsSections = CIBlockSection::getList($sort, $filter, $cnt, $select);
		while($section = $rsSections->GetNext())
        {
			if(!empty($section['PICTURE']))
				$section['PICTURE'] = CFile::getFileArray($section['PICTURE']);
				
			if(!empty($section['DETAIL_PICTURE']))
				$section['DETAIL_PICTURE'] = CFile::getFileArray($section['DETAIL_PICTURE']);
				
			$sections[$section['ID']] = $section;
		}
		return $sections;
	}
	
	public static function getNotEmptySections($filter = array('sections' => array(), 'elements' => array()), $sort = array())
    {
        CModule::includeModule('iblock');
		$sections = array();
		
		$rsSections = CIBlockSection::getList($sort, $filter['sections']);
		while($section = $rsSections->getNext())
        {
			$sections[$section['ID']] = $section;
		}
		
		$tmpSections = $sections;
		$sections = array();
		$rsElements = CIBlockElement::getList(array('SORT'), $filter['elements'], array('IBLOCK_SECTION_ID'));
		while($element = $rsElements->getNext())
        {
			if($element['CNT'] > 0)
				$sections[$element['IBLOCK_SECTION_ID']] = $tmpSections[$element['IBLOCK_SECTION_ID']];
		}
		
		return $sections;
	}
	
	public static function iconv($from, $to, $data)
    {
		if(is_array($data))
        {
			foreach($data as &$v)
            {
				$v = nm::iconv($from, $to, $v);
			}
		}else
			$data = iconv($from, $to, $data);
		
		return $data;
	}
    
    public static function cache($cacheTime = 3600, $cacheId, $cacheDir, $func, $arg=array())
    {
		if(!$func) return;
        
		$obCache = new CPHPCache;
		if($cacheTime > 0 && $obCache->InitCache($cacheTime, $cacheId, $cacheDir))
        {
			$res = $obCache->GetVars();
		}
        elseif($obCache->StartDataCache())
        {
			$res = call_user_func_array($func, $arg);
			$obCache->EndDataCache($res); 
		}
		return $res;		
	}
    
    // Пользовательские свойства
    public static function getUserField ($entity_id, $value_id, $property_id) 
    {
        CModule::IncludeModule("iblock");
        $arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entity_id, $value_id); 
        return $arUF[$property_id]["VALUE"]; 
    } 

    public static function setUserField ($entity_id, $value_id, $uf_id, $uf_value) 
    {
        CModule::IncludeModule("iblock");
        return $GLOBALS["USER_FIELD_MANAGER"]->Update($entity_id, $value_id, Array ($uf_id => $uf_value)); 
    }
    
    public static function GUID()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
    
    public static function translit($str, $lang, $params = array())
	{
        static $search = array();

		if(!isset($search[$lang]))
		{
			$mess = IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/js_core_translit.php", $lang, true);
			$trans_from = explode(",", $mess["TRANS_FROM"]);
			$trans_to = explode(",", $mess["TRANS_TO"]);
			foreach($trans_from as $i => $from)
				$search[$lang][$from] = $trans_to[$i];
		}

		$defaultParams = array(
			"max_len" => 100,
			"change_case" => 'L', // 'L' - toLower, 'U' - toUpper, false - do not change
			"replace_space" => '_',
			"replace_other" => '_',
			"delete_repeat_replace" => true,
			"safe_chars" => '',
		);
		foreach($defaultParams as $key => $value)
			if(!array_key_exists($key, $params))
				$params[$key] = $value;

		$len = strlen($str);
		$str_new = '';
		$last_chr_new = '';

		for($i = 0; $i < $len; $i++)
		{
			$chr = mb_substr($str, $i, 1, 'UTF-8');

			if(preg_match("/[a-zA-Z0-9]/".BX_UTF_PCRE_MODIFIER, $chr) || strpos($params["safe_chars"], $chr)!==false)
			{
				$chr_new = $chr;
			}
			elseif(preg_match("/\\s/".BX_UTF_PCRE_MODIFIER, $chr))
			{
				if (
					!$params["delete_repeat_replace"]
					||
					($i > 0 && $last_chr_new != $params["replace_space"])
				)
					$chr_new = $params["replace_space"];
				else
					$chr_new = '';
			}
			else
			{
				if(array_key_exists($chr, $search[$lang]))
				{
					$chr_new = $search[$lang][$chr];
				}
				else
				{
					if (
						!$params["delete_repeat_replace"]
						||
						($i > 0 && $i != $len-1 && $last_chr_new != $params["replace_other"])
					)
						$chr_new = $params["replace_other"];
					else
						$chr_new = '';
				}
			}

			if(strlen($chr_new))
			{
				if($params["change_case"] == "L" || $params["change_case"] == "l")
					$chr_new = ToLower($chr_new);
				elseif($params["change_case"] == "U" || $params["change_case"] == "u")
					$chr_new = ToUpper($chr_new);

				$str_new .= $chr_new;
				$last_chr_new = $chr_new;
			}

			if (strlen($str_new) >= $params["max_len"])
				break;
		}
        
        if(substr($str_new, -1)=="-")
        {
            $str_new = substr($str_new, 0, -1); 
        }

		return $str_new;
	}
    
    /**
     **Функция склонения слова в зависимости от числа
    */
    public static function number_ending($number, $ending0, $ending1, $ending2) 
    {
        $num100 = $number % 100;
        $num10 = $number % 10;
        if ($num100 >= 5 && $num100 <= 20) {
          return $ending0;
        } else if ($num10 == 0) {
          return $ending0;
        } else if ($num10 == 1) {
          return $ending1;
        } else if ($num10 >= 2 && $num10 <= 4) {
          return $ending2;
        } else if ($num10 >= 5 && $num10 <= 9) {
          return $ending0;
        } else {
          return $ending2;
        }
    }
    
    public static function ageToSvg($age)
    {
        $age = intval($age);
        $array = array(
            0 => "zero",
            6 => "six",
            12 => "twelve",
            16 => "sixteen",
            18 => "eighteen"
        );
        
        return $array[$age];
    }
    
    public static function show_grayscale($filename)
    {
        $img_size = GetImageSize($filename);
        $width = $img_size[0];
        $height = $img_size[1];
        $img = imageCreate($width,$height);
        for ($c = 0; $c < 256; $c++) {
            ImageColorAllocate($img, $c,$c,$c);
        }
        $img2 = ImageCreateFromJpeg($filename);
        ImageCopyMerge($img,$img2,0,0,0,0, $width, $height, 100);
        
        imagejpeg($img);
        
        imagedestroy($img);
    }
    
    public static function resizeImage($picture, $width, $height)
    {
        if(!is_array($picture))
        {
            $rsFile = CFile::GetByID($picture);
            $arFile = $rsFile->Fetch();
        }else{
            $arFile = $picture;
        }
        
        //CDev::pre($arFile);
        
        if($arFile["HEIGHT"]>$height || $arFile["WIDTH"]>$width)
        {
            $arFileTmp = CFile::ResizeImageGet(
                $arFile,
                array(
                   "width" => $width, 
                   "height" => $height
                ),
                BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                true,
                array() //убираем черный фон у прозрачных изображений
            );
            
            $arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);
        
            return array(
                "SRC" => $arFileTmp["src"],
                "WIDTH" => IntVal($arSize[0]),
                "HEIGHT" => IntVal($arSize[1]),
            );
        }else{
            $arFile["SRC"] = CFile::GetPath($arFile["ID"]);
            return $arFile;
        }
        
        /*if($arFile["HEIGHT"]==$height || $arFile["WIDTH"]==$width)
        {
            $arFile["SRC"] = CFile::GetPath($arFile["ID"]);
            return $arFile;
        }
        
        $arFileTmp = CFile::ResizeImageGet(
            $arFile,
            array(
               "width" => $width, 
               "height" => $height
            ),
            BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
            true,
            array() //убираем черный фон у прозрачных изображений
        );
        
        $arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);
    
        return array(
            "SRC" => $arFileTmp["src"],
            "WIDTH" => IntVal($arSize[0]),
            "HEIGHT" => IntVal($arSize[1]),
        );*/
    }
    
    public static function check_email($email)
    {
        if (preg_match("/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:ac|ae|aero|ag|am|asia|at|au|be|bg|biz|bj|br|ca|ch|ci|cl|cn|com|coop|cx|cz|de|dk|edu|ee|fi|fr|gd|gg|gi|gov|gs|gw|gy|hk|hn|ie|il|in|info|int|io|ir|is|it|je|jobs|jp|ke|kg|ki|kr|kza|la|li|lt|lu|lv|ly|ma|mg|me|mil|mn|mobi|ms|museum|mx|my|na|name|net|nl|no|nu|nz|org|pl|pm|pr|pro|re|ro|ru|sa|sb|sc|se|sg|sh|si|st|tc|tel|tf|tk|tl|tm|tr|travel|tv|tw|ua|ug|uk|us|uz|vc|ve|vg|wf|ws|yt)$/i", $email))
        {
            return true;
        }
        return false;
    }
    
    public static function check_phone($phone)
    {
        if (preg_match("/^([0-9]{11})$/", $phone))
        {
            return true;
        }
        return false;
    }
    
    public static function deleteDirectory($dirPath) 
    {
        if (is_dir($dirPath)) 
        {
            $objects = scandir($dirPath);
            foreach ($objects as $object) 
            {
                if ($object != "." && $object !="..") 
                {
                    if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") 
                    {
                        self::deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dirPath . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dirPath);
        }
    }
    
    public static function deleteOldFiles($dir, $expire_time)
    {
        // проверяем, что $dir - каталог
        if (is_dir($dir)) 
        {
            // открываем каталог
            if ($dh = opendir($dir)) 
            {
                // читаем и выводим все элементы от первого до последнего
                while (($file = readdir($dh)) !== false) 
                {
                    // текущее время
                    $time_sec=time();
                    
                    // время изменения файла
                    $time_file=filemtime($dir . $file);
                    
                    // теперь узнаем сколько прошло времени (в секундах)
                    $time=$time_sec-$time_file;
                    
                    $unlink = $dir.$file;
                    
                    if (is_file($unlink))
                    {
                        if ($time>$expire_time)
                        {
                            unlink($unlink);
                        }
                    }
                }
                // закрываем каталог
                closedir($dh);
            }
        }
    }    
}