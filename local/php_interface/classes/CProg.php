<?
IncludeModuleLangFile(__FILE__);
class CProg
{
    public static $cacheDir = "progs";
    
    public static function generateUnique($arFields)
    {
        $str = $arFields["CHANNEL"]."-".htmlspecialchars_decode($arFields["NAME"]); //!!!!!!
        //$str = htmlspecialchars_decode($arFields["NAME"]."-".$arFields["DESC"]."-".$arFields["ACTOR"]."-".$arFields["PRESENTER"]);
        
        $arParams = array("replace_space"=>"-", "replace_other"=>"-");
        $str = CDev::translit($str, "ru", $arParams);
        return $str;
    }
    
    public static function getByID($ID, $arSelect = false)
    {
        CModule::IncludeModule("iblock");
        
        if(!$ID)
            return false;
        
        if(empty($arSelect))
            $arSelect = Array("ID", "NAME");
        
        $arProgs = array();
        $arFilter = array("IBLOCK_ID" => PROG_IB, "ACTIVE" => "Y", "=ID" => $ID);
        
        $CacheEx = new CCacheEx(60*60*24*365, self::$cacheDir);
        $arProg = $CacheEx->cacheElement( array( "SORT" => "ASC", "ID" => "DESC" ), $arFilter, "getlist", false, $arSelect);
        
        return $arProg[0];
    }
    
    public static function getList($arrFilter=false, $arSelect = array(), $arSort=false )
    {
        CModule::IncludeModule("iblock");
        $arProgs = array();
        
        if(empty($arSelect))
            $arSelect = Array("ID", "NAME", "PREVIEW_TEXT", "PROPERTY_CHANNEL", "PROPERTY_SUB_TITLE");
            
        $arFilter = array("IBLOCK_ID" => PROG_IB, "ACTIVE" => "Y");
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        $CacheEx = new CCacheEx(60*60*24*365, self::$cacheDir);
        
        if(!$arSort)
            $arSort = array( "SORT" => "ASC", "ID" => "DESC" );
        
        $arTmpProgs = $CacheEx->cacheElement($arSort , $arFilter, "getlist", false, $arSelect);
        foreach( $arTmpProgs as $arTmpProg )
        {
            if(!empty($arTmpProg["PROPERTY_SUB_TITLE_VALUE"]))
            {
                $name = $arTmpProg["NAME"]." (".trim($arTmpProg["PROPERTY_SUB_TITLE_VALUE"]).")";
            }else{
                $name = $arTmpProg["NAME"];
            }
            
            if($arTmpProg["PROPERTY_CHANNEL_VALUE"] && $name)
            {
                $unique = self::generateUnique(array(
                    "CHANNEL" => $arTmpProg["PROPERTY_CHANNEL_VALUE"],
                    "NAME" => $name,
                    "DESC" => $arTmpProg["PREVIEW_TEXT"],
                    "ACTOR" => $arTmpProg["PROPERTY_ACTOR_VALUE"],
                    "PRESENTER" => $arTmpProg["PROPERTY_PRESENTER_VALUE"],
                ));
                $arProgs[$unique] = $arTmpProg;
            }else{
                $arProgs[] = $arTmpProg;
            }
		}
        
        return $arProgs;
    }
    
    public static function add($arFields)
    {
        CModule::IncludeModule("iblock");
        $el = new CIBlockElement;
        
        $PROP = array();
        $PROP = $arFields["PROPS"];
        
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID"      => PROG_IB,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => trim($arFields["FIELDS"]["NAME"]),
            "ACTIVE"         => "Y",
        );
        
        $arLoadProductArray = array_merge($arLoadProductArray, $arFields["FIELDS"]);
        /*if(!empty($PROP["SUB_TITLE"]))
        {
            $arLoadProductArray["NAME"] = $arLoadProductArray["NAME"]." (".$PROP["SUB_TITLE"].")";
        }*/
        
        $prog_id = $el->Add($arLoadProductArray);
        if($prog_id)
        {
            return $prog_id;
        }else{
            return $el->LAST_ERROR;
        }
    }
    
    /**
     * Delete old pics from upload/iblock
     */
    public static function deletePics($arrFilter = false)
    {
        CModule::IncludeModule("iblock");
        global $DB;
        
        $arSelect = Array("ID","PREVIEW_PICTURE", 'DETAIL_PICTURE', "PROPERTY_PICTURE_DOUBLE", "PROPERTY_PICTURE_HALF", 
            "PROPERTY_PICTURE_VERTICAL", "PROPERTY_PICTURE_VERTICAL_DOUBLE");
            
        $arFilter = array("IBLOCK_ID" => PROG_IB); 
        
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        $arProgs = CIBlockElement::GetList( array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
		while( $arProg = $arProgs->GetNext() )
        {
            $arProps = array("PICTURE_DOUBLE", "PICTURE_HALF", "PICTURE_VERTICAL", "PICTURE_VERTICAL_DOUBLE");
            foreach($arProps as $code)
            {
                CFile::Delete($arProg["PROPERTY_".$code."_VALUE"]);
            }
            
            CFile::Delete($arProg["PREVIEW_PICTURE"]);
            CFile::Delete($arProg["DETAIL_PICTURE"]);
        }
    }
    
    public static function delete($arrFilter = false) 
    {
		CModule::IncludeModule("iblock");
        global $DB;
        
        $arProgs = array();
        $arSelect = Array("PROPERTY_PROG", "ID");
        $arFilter = array(
            "IBLOCK_ID" => PROG_TIME_IB, "ACTIVE" => "Y", 
            ">=PROPERTY_DATE"=>date('Y-m-d', strtotime("-2 day", strtotime(date("d.m.Y"))))
        ); 
        $rsRes = CIBlockElement::GetList( array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
		while( $arItem = $rsRes->GetNext() )
        {
            $arProgs[] = $arItem["PROPERTY_PROG_VALUE"];
            CIBlockElement::Delete($arItem["ID"]);
		}
        $arProgs = array_unique($arProgs);
        
        $arSelect = Array("ID");
        $arFilter = array("IBLOCK_ID" => PROG_IB); 
        
        if($arrFilter)
            $arFilter = array_merge($arFilter, $arrFilter);
        
        self::deletePics(array_merge($arFilter, array("ID"=>$arProgs)));

        $rsRes = CIBlockElement::GetList( array("SORT" => "ASC"), $arFilter, false, false, $arSelect);
		while( $arItem = $rsRes->GetNext() )
        {
            if(!in_array($arItem["ID"], $arProgs))
            {
                $DB->StartTransaction();
                CIBlockElement::Delete($arItem["ID"]);
                $DB->Commit();
            }  
		}
        
        self::updateCache();
	}
    
    public static function addRating($ID, $addRating)
    {
        $arProg = self::getByID($ID, array("PROPERTY_RATING"));
        $rating = intval($arProg["PROPERTY_RATING_VALUE"]) + intval($addRating);
        
        CIBlockElement::SetPropertyValueCode($ID, "RATING", $rating);
    }
    
    public static function updateCache() 
    {
		CCacheEx::clean(self::$cacheDir);
	}
}