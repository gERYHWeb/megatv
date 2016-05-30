<?
class CSocialAuth
{
    public static function getUserByProviderAndId($providerName, $userProfile)
    {
        CModule::IncludeModule("iblock");
        
        $userID = false;
        
        //Проверим привязан ли к какому нибудь пользователю
        $arrFilter = array(
            "IBLOCK_ID" => USER_SOCIAL_IB,
            "ACTIVE" => "Y",
            "PROPERTY_SOCIAL_PROVIDER" => $providerName,
            "PROPERTY_SOCIAL_ID" => $userProfile["identifier"]
        );
        $arSelect = array("PROPERTY_USER_ID");
        $rsRes = \CIBlockElement::GetList( $arOrder, $arrFilter, false, false, $arSelect );
		if( $arItem = $rsRes->GetNext() )
        {
            $userID = intval($arItem["PROPERTY_USER_ID_VALUE"]);
		}
        
        $email = "";
        if(!empty($userProfile["email"]))
        {
            $email = $userProfile["email"];
        }
        else if(!empty($userProfile["emailVerified"]))
        {
            $email = $userProfile["emailVerified"];
        }
        
        //Проверяем есть ли пользователь с таким мэйлом
        if(!$userID && !empty($email))
        {
            $dbUsers = CUser::GetList(($by="EMAIL"), ($order="desc"), Array("=EMAIL" =>$email));
            while($arUser = $dbUsers->Fetch())
            {
                $userID = $arUser["ID"];
                self::connectToUser($userID, $providerName, $userProfile);
                self::updateUser($userID, $userProfile);
            } 
        }
        
        return $userID;
    }
    
    public static function connectToUser($userID, $providerName, $userProfile)
    {
        CModule::IncludeModule("iblock");
        $el = new \CIBlockElement;
        
        $PROP = array();
        $PROP["USER_ID"] = $userID;
        $PROP["SOCIAL_PROVIDER"] = $providerName;
        $PROP["SOCIAL_ID"] = $userProfile["identifier"];
        
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => USER_SOCIAL_IB,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => trim("Пользователь №".$userID),
            "ACTIVE"         => "Y",
        );
        
        $el->Add($arLoadProductArray);
        
        \CUserEx::capacityAdd($userID, 1);
    }
    
    public static function createUser($providerName, $userProfile)
    {
        global $USER;
        COption::SetOptionString("main","captcha_registration","N");
        $default_group = COption::GetOptionString("main", "new_user_registration_def_group");
        if(!empty($default_group))
            $arrGroups = explode(",", $default_group);
        
        $password = mb_substr(md5(uniqid(rand(),true)), 0, 8);
        
        $birthday = $userProfile["birthDay"].".".$userProfile["birthMonth"].".".$userProfile["birthYear"];
        if(strlen($birthday)!=10)
        {
            $birthday = "";
        }
        
        $email = "";
        if(!empty($userProfile["email"]))
        {
            $email = $userProfile["email"];
        }
        else if(!empty($userProfile["emailVerified"]))
        {
            $email = $userProfile["emailVerified"];
        }
        
        if(empty($userProfile["firstName"]) && empty($userProfile["lastName"]))
        {
            $userProfile["firstName"] = $userProfile["displayName"];
        }
        
        $cUser = new \CUser; 
        $arFields = Array(
			"NAME"              => $userProfile["firstName"],
            "LAST_NAME"         => $userProfile["lastName"],
			"EMAIL"             => $email,
			"LOGIN"             => $email,
			"PERSONAL_GENDER"	=> strtoupper(substr($userProfile["gender"], 0, 1)),
			"PERSONAL_WWW"		=> $arResult['USER']["URL"],
			"PERSONAL_BIRTHDAY"	=> $birthday,
			"ACTIVE"            => "Y",
			"GROUP_ID"          => $arrGroups,
			"EXTERNAL_AUTH_ID"	=> $providerName.$userProfile["identifier"],
			"PASSWORD"          => $password,
			"CONFIRM_PASSWORD"  => $password,
            "PERSONAL_PHONE"    => $userProfile["phone"],
            "PERSONAL_CITY"     => $userProfile["home_town"]
		);	
        
        //$json = file_get_contents('https://graph.facebook.com/'.$userProfile["identifier"].'/picture?type=large');
        //$file = $_SERVER["DOCUMENT_ROOT"].'/upload/avatar/'.$userProfile["identifier"].'.jpg';
        
        if(!empty($userProfile["photoURL"]))
        {
            if($providerName=="facebook")
            {
                $img = file_get_contents('https://graph.facebook.com/'.$userProfile["identifier"].'/picture?type=large');
                $file = $_SERVER["DOCUMENT_ROOT"].'/upload/avatar/'.$userProfile["identifier"].'.jpg';
                file_put_contents($file, $img);
            }
            else if($providerName=="yandex")
            {
                $img = file_get_contents('https://avatars.yandex.net/get-yapic/'.$userProfile["identifier"].'/islands-200');
                $file = $_SERVER["DOCUMENT_ROOT"].'/upload/avatar/'.$userProfile["identifier"].'.jpg';
                file_put_contents($file, $img);
            }
            else if($providerName=="linkedin")
            {
                $img = file_get_contents($userProfile["photoURL"]);
                $file = $_SERVER["DOCUMENT_ROOT"].'/upload/avatar/'.$userProfile["identifier"].'.jpg';
                file_put_contents($file, $img);
            }
            else{
                $file = $userProfile["photoURL"];
            }
            
            $arImage = \CFile::MakeFileArray($file);
            $arImage["MODULE_ID"] = "main";
            $arFields["PERSONAL_PHOTO"] = $arImage;
        }
        
        $USER_ID = $cUser->Add($arFields);
        if($USER_ID)
        {
            \CUser::SendUserInfo($USER_ID, SITE_ID, "Приветствуем Вас как нового пользователя нашего сайта!");
        
            if(!empty($email))
            {
                \CUserEx::capacityAdd($USER_ID, 1);   // за мэйл +1ГБ
                
                $fields = array();
                $fields["EXTERNAL_AUTH_ID"] = "";
                $fields["PASSWORD"] = $password;
                $fields["CONFIRM_PASSWORD"] = $password;
                $сuser = new \CUser;
                $сuser->Update($USER_ID, $fields);
                
                $arEventFields = array(
                    "USER_NAME"             => trim($userProfile["firstName"]." ".$userProfile["lastName"]),
                    "PASSWORD"          	=> $password,
                    "EMAIL"			        => $email,
                );
                CEvent::Send("USER_PASS_CHANGED_PROFILE", SITE_ID, $arEventFields);

                $USER->Login($email, $password, 'N');
            }
            
            //Бонус за регистрацию
            \CUserEx::capacityAdd($USER_ID, BONUS_FOR_REGISTRATION);
            
            self::connectToUser($USER_ID, $providerName, $userProfile);
        }else{
            echo $cUser->LAST_ERROR;
        }
        
        COption::SetOptionString("main", "captcha_registration", "Y");
        
        return $USER_ID;
    }
    
    public static function updateUser()
    {
        
    }
}