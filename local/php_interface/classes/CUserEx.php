<?
class CUserEx
{
    function OnBeforeUserLogin($arFields)
    {
        $phone = preg_replace("/[^0-9]/", '', $arFields["LOGIN"]);
        
        if(\CDev::check_phone($phone))
        {
            $filter = Array("PERSONAL_PHONE" =>$phone);
        }else{
            $filter = Array("=EMAIL" =>$arFields["LOGIN"]);
        }
        
        $rsUsers = \CUser::GetList(($by="LAST_NAME"), ($order="asc"), $filter);
        if($user = $rsUsers->GetNext())
            $arFields["LOGIN"] = $user["LOGIN"];
    }
    
    function OnBeforeUserRegister($arFields)
    {
        $arFields["LOGIN"] = $arFields["EMAIL"];
        $arFields["PERSONAL_BIRTHDAY"] = $arFields["USER_PERSONAL_BIRTHDAY"];
    }
    
    function OnBeforeUserSendPasswordHandler($arFields)
    {
        /*$rsUser = CUser::GetByID($arFields["ID"]);
        $arUser = $rsUser->Fetch();
        
        $rsUser = CUser::GetByLogin($email);
        if($arUser["UF_PHONE_REG"]=="Y")
        {
            $text = "Проверочное слово: ".$arFields["USER_CHECKWORD"];
            CEchogroupSmsru::Send($arUser["PERSONAL_PHONE"], $text);
        }*/
    }
    
    function OnBeforeUserDeleteHandler($user_id)
    {
        \CModule::IncludeModule("iblock");
        \CModule::IncludeModule("sale");
        
        //Привязки к соц. сетям
        $arrFilter = array(
            "IBLOCK_ID" => USER_SOCIAL_IB,
            "PROPERTY_USER_ID" => $user_id,
        );
        $arSelect = array("ID");
        $rsRes = \CIBlockElement::GetList( $arOrder, $arrFilter, false, false, $arSelect );
		while( $arItem = $rsRes->GetNext() )
        {
            \CIBlockElement::Delete($arItem["ID"]);
		}
        
        //Удаляем записи
        $result = \Hawkart\Megatv\RecordTable::getList(array(
            'filter' => array("UF_USER_ID"=>$user_id),
            'select' => array("ID"),
        ));
        while ($arRecord = $result->fetch())
        {
            \CRecordEx::delete($arRecord["ID"]);
        }
        
        //Удаляем счет
        if($arAccount = \CSaleUserAccount::GetByUserID($user_id, "RUR"))
        {
            \CSaleUserAccount::Delete($arAccount["ID"]);
        }
        
        //Удаляем заказы
        $arFilter = Array(
           "USER_ID" => $user_id,
        );
        $db_sales = \CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
        while ($ar_sales = $db_sales->Fetch())
        {
            \CSaleOrder::Delete($ar_sales["ID"]);
        }
        
        //Удаляем подписки
        $result = \Hawkart\Megatv\SubscribeTable::getList(array(
            'filter' => array("=UF_USER_ID" => $user_id),
            'select' => array("ID")
        ));
        if ($arSub = $result->fetch())
        {
            \Hawkart\Megatv\SubscribeTable::delete($arSub["ID"]);
        }

    }
    
    public static function generateDataSotal()
    {
        global $USER;
        $USER_ID = $USER->GetID();
        $rsUser = \CUser::GetByID($USER_ID);
        $arUser = $rsUser->Fetch();

        $password = mb_substr(md5(uniqid(rand(),true)), 0, 12);
        
        $cUser = new \CUser;
        $cUser->Update($USER_ID, array(
            "UF_SOTAL_LOGIN" => "email_".$USER_ID."@"."megatv.ru",
            "UF_SOTAL_PASS" => $password
        ));
        
        $arUser["UF_SOTAL_LOGIN"] = "email_".$USER_ID."@"."megatv.ru";
        $arUser["UF_SOTAL_PASS"] = $password;
        
        return $arUser;
    }
    
    /**
     * При загрузке аватара уменьшаем его размер до 150х150px
     */
    public static function updateAvatar($USER_ID)
    {
        $imageMaxWidth = 216; // Максимальная ширина уменьшенной картинки 
        $imageMaxHeight = 216; // Максимальная высота уменьшенной картинки
        
        $rsUser = \CUser::GetByID($USER_ID);
        $arUser = $rsUser->Fetch();
        
        if(intval($arUser["PERSONAL_PHOTO"])>0)
        {
            $arFile = \CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
            
            // проверяем, что файл является картинкой
            if (!\CFile::IsImage($arFile["FILE_NAME"]))
            {
                echo "не является картинкой";
                continue;
            }
                
            // Если размер больше допустимого
            if ($arFile["WIDTH"] > $imageMaxWidth || $arFile["HEIGHT"] > $imageMaxHeight)
            {
                // Временная картинка
                $tmpFilePath = $_SERVER['DOCUMENT_ROOT']."/upload/tmp/".$arFile["FILE_NAME"];
                
                // Уменьшаем картинку
                $resizeRez = \CFile::ResizeImageFile( // уменьшение картинки для превью
                    $source = $_SERVER['DOCUMENT_ROOT'].$arFile["SRC"],
                    $dest = $tmpFilePath,
                    array(
                        'width' => $imageMaxWidth,
                        'height' => $imageMaxHeight
                    ),
                    $resizeType = BX_RESIZE_IMAGE_EXACT,//BX_RESIZE_IMAGE_PROPORTIONAL, // метод ресайза
                    $waterMark = array(), // водяной знак (пустой)
                    $jpgQuality = 95 // качество уменьшенной картинки в процентах
                );
                
                // Записываем изменение в свойство
                if ($resizeRez && $tmpFilePath) 
                {
                    $arNewFile = \CFile::MakeFileArray($tmpFilePath);

                    $arNewFile['del'] = "Y";
                    $arNewFile['old_file'] = $arUser['PERSONAL_PHOTO'];
                    $arNewFile["MODULE_ID"] = "main";
                    $fields['PERSONAL_PHOTO'] = $arNewFile;
                    
                    $user = new \CUser;
                    $user->Update($arUser["ID"], $fields);
                    
                    $rsUser = \CUser::GetByID($USER_ID);
                    $arUser = $rsUser->Fetch();
                    
                    // Удалим временный файл
                    unlink($tmpFilePath);
                } 
            }
        }
        
        return $arUser;
    }
    
    //Добавляем подписку на бесплатные каналы по умолчанию
    public function OnAfterUserUpdateHandler(&$arFields)
    {
        if(intval($arFields["ID"])>0)
        {
            $result = \Hawkart\Megatv\ChannelTable::getList(array(
                'filter' => array("UF_ACTIVE" => 1, "!UF_PRICE_H24" => true, "!UF_FORBID_REC"=>1),
                'select' => array("ID")
            ));
            while ($arChannel = $result->fetch())
            {
                $CSubscribe = new \Hawkart\Megatv\CSubscribe("CHANNEL");
                $CSubscribe->setUserSubscribe($arChannel["ID"], $arFields["ID"]);
            }
        }
    }
    
    public static function getBudget($USER_ID=false)
    {
        return \CSaleAccountEx::budget($USER_ID);
    }
    
    public static function capacityAdd($USER_ID, $gb)
    {
        global $USER;
        if(!$USER_ID)
            $USER_ID = $USER->GetID();
            
        $rsUser = \CUser::GetByID($USER_ID);
        $arUser = $rsUser->Fetch();
        
        $capacity = $arUser["UF_CAPACITY"] + $gb;
        
        $cuser = new \CUser;
        $cuser->Update($arUser["ID"], array(
            "UF_CAPACITY" => $capacity
        ));
    }
        
}