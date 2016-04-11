<?
class CNotifyEx
{
    /**
    * В момент постановки на запись уведомление пользователю
    * 
    * @param array $arParams
    * @return boolean
    */
    public static function onRecord($arParams)
    {
        $rsUser = CUser::GetByID($arParams["USER_ID"]);
        $arUser = $rsUser->Fetch();
        
        if(!empty($arUser["EMAIL"]))
        {
            $arEventFields = array(
                "RECORD_ID" => $arParams["RECORD_ID"],
                "EMAIL" => $arUser["EMAIL"],
                "USER_ID" => $arParams["USER_ID"],
                "USER_NAME" => $arUser["NAME"],
                "RECORD_TITLE" => $arParams["RECORD_NAME"],
            );
            if(CEvent::Send("RECORD_ON", array(SITE_ID), $arEventFields))
            {
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
    
    /**
    * После окончания записи передачи уведомление пользователю
    * 
    * @param array $arParams
    * @return boolean
    */
    public static function afterRecord($arParams)
    {
        if(!empty($arParams["USER_EMAIL"]))
        {
            $arEventFields = array(
                "RECORD_ID" => $arParams["RECORD_ID"],
                "RECORD_TITLE" => $arParams["RECORD_NAME"],
                "EMAIL" => $arParams["USER_EMAIL"],
                "USER_ID" => $arParams["USER_ID"],
                "USER_NAME" => $arParams["USER_NAME"],
                "URL" => $arParams["URL"],
                "PICTURE" => $arParams["PICTURE"],
            );
            if(CEvent::Send("RECORD_AFTER", array(SITE_ID), $arEventFields))
            {
                return true;
            }else{
                return false;
            }
        }else{
            return true;
        }
    }
}