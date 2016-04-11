<?
class CRecordEx
{
    public static function getList($arrFilter = false, $arSelect = false, $arOrder = false)
    {
        global $USER;
        CModule::IncludeModule('highloadblock');
        CModule::IncludeModule('iblock');
        $arRecords = array();
        
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(RECORD_HL)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        
        $arFilter = array();
        
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        if(!$arOrder)
            $arOrder = array("ID" => "ASC");
        
        $rsData = $entity_data_class::getList(array(
        	'filter' => $arFilter,
        	'select' => $arSelect,
        	'limit' => false,
        	'order' => $arOrder,
        ));
        while($arTmp = $rsData->Fetch()) 
        {
            $arRecords[] = $arTmp;
        }
        
        return $arRecords;
    }
    
    
    public static function getBySotalID($sotal_id, $arSelect=false)
    {
        $arSel = array("ID");
        if($arSelect)
            $arSel = array_merge($arSel, $arSelect);
            
        $arRecords = self::getList(array("UF_SOTAL_ID"=>$sotal_id), $arSel);
        
        if(!empty($arRecords))
            return $arRecords[0];
        
        return false;
    } 
    
    public static function getByID($id)
    {
        CModule::IncludeModule('highloadblock');
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(RECORD_HL)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        
        $arRecord = $entity_data_class::getById($id)->fetch();
        
        return $arRecord;
    }   
    
    public static function create($arFields)
    {
        CModule::IncludeModule('highloadblock');
        global $USER;
        
        if(!$USER_ID)
            $USER_ID = $USER->GetID();
        
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(RECORD_HL)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
        
        $arProgTime = CProgTime::getByID($arFields["UF_SCHEDULE"], array("PROPERTY_DATE_END", "PROPERTY_PROG", "PROPERTY_DATE_START"));
        $start = new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', strtotime($arProgTime["PROPERTY_DATE_START_VALUE"])), 'Y-m-d H:i:s');
        $end = new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', strtotime($arProgTime["PROPERTY_DATE_END_VALUE"])), 'Y-m-d H:i:s');
        
        $data = array(
           'UF_USER' => $USER_ID,
           'UF_DATE_START' => $start,
           'UF_DATE_END' => $end,
           'UF_SOTAL_ID' => $arFields["UF_SOTAL_ID"],
           'UF_SCHEDULE' => $arFields["UF_SCHEDULE"],
           'UF_PROG' => $arProgTime["PROPERTY_PROG_VALUE"]
        );
        
        $arProg = CProg::getByID($arProgTime["PROPERTY_PROG_VALUE"], array("NAME", "PROPERTY_SUB_TITLE", "PREVIEW_PICTURE", "PROPERTY_PICTURE_DOUBLE", "PROPERTY_CATEGORY"));
        $data["UF_NAME"] = $arProg["NAME"];
        $data["UF_SUB_TITLE"] = $arProg["PROPERTY_SUB_TITLE_VALUE"];
        
        $picture = CFile::GetPath($arProg["PREVIEW_PICTURE"]);
        $picture_double = CFile::GetPath($arProg["PROPERTY_PICTURE_DOUBLE_VALUE"]);
        $data["UF_PICTURE"] = CFile::MakeFileArray($picture);
        $data["UF_PICTURE_DOUBLE"] = CFile::MakeFileArray($picture_double);
        
        $data["UF_CATEGORY"] = $arProg["PROPERTY_CATEGORY_VALUE"];
                         
        $result = $entity_data_class::add($data);
        if ($result->isSuccess()) 
        {         
            return true;        
        }
        else
        { 
            return implode(', ', $result->getErrors());
        }
    }
    
    public static function update($ID, $arFields)
    {
        CModule::IncludeModule('highloadblock');
        
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(RECORD_HL)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
                   
        $result = $entity_data_class::update($ID, $arFields);    
        if ($result->isSuccess()) 
        {         
            return true;        
        }
        else
        { 
            return implode(', ', $result->getErrors());
        }        
    }
    
    public static function updateFromProg()
    {
        $arRecords = self::getList(false, array("UF_PROG", "ID"));
        foreach($arRecords as $arRecord)
        {
            $data = array();
            $arProg = CProg::getByID($arRecord["UF_PROG"], array("NAME", "PROPERTY_SUB_TITLE", "PREVIEW_PICTURE", "PROPERTY_PICTURE_DOUBLE"));
            $data["UF_NAME"] = $arProg["NAME"];
            $data["UF_SUB_TITLE"] = $arProg["PROPERTY_SUB_TITLE_VALUE"];
            
            $picture = CFile::GetPath($arProg["PREVIEW_PICTURE"]);
            $picture_double = CFile::GetPath($arProg["PROPERTY_PICTURE_DOUBLE_VALUE"]);
            $data["UF_PICTURE"] = CFile::MakeFileArray($picture);
            $data["UF_PICTURE_DOUBLE"] = CFile::MakeFileArray($picture_double);
            
            self::update($arRecord["ID"], $data);
        }
    }
    
    public static function delete($ID)
    {
        CModule::IncludeModule('highloadblock');
        
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById(RECORD_HL)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity( $hlblock );
        $entity_data_class = $entity->getDataClass();
                   
        $result = $entity_data_class::delete($ID);       
    }
}