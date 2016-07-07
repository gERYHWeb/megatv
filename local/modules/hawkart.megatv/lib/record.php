<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class RecordTable extends Entity\DataManager
{
    protected static $cut_dir = "/upload/record_cut/";
    
    /**
     * Delete pic after delete record
     */
    public static function onAfterDelete(Entity\Event $event)
    {
        $id = $event->getParameter('id');
        
        $result = self::getList(array(
            'filter' => array(
                "!ID" => $id, 
            ),
            'select' => array(
                "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
            ),
            'limit' => 1
        ));
        $arRecord = $result->fetch();

        if(!empty($arRecord["UF_IMG_PATH"]))
        {
            unlink(CFile::getCropedPath($arRecord["UF_IMG_PATH"], array(300, 300), true));
        }
    }
    
    
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_record';
	}
    
    public static function create($arFields)
    {
        global $USER;
        
        if(!$USER_ID)
            $USER_ID = $USER->GetID();
            
        $start = new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', strtotime($arFields["UF_DATE_START"])), 'Y-m-d H:i:s');
        $end = new \Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', strtotime($arFields["UF_DATE_END"])), 'Y-m-d H:i:s');
        
        $data = array(
           'UF_USER_ID' => $USER_ID,
           'UF_DATE_START' => $start,
           'UF_DATE_END' => $end,
           'UF_SOTAL_ID' => $arFields["UF_SOTAL_ID"],
           'UF_CHANNEL_ID' => $arFields["UF_CHANNEL_ID"],
           'UF_SCHEDULE_ID' => $arFields["ID"],
           'UF_PROG_ID' => $arFields["UF_PROG_ID"]
        );
        
        $result = self::add($data);
        if ($result->isSuccess()) 
        {   
            
            /**
             * Create image croped for record
             * \Hawkart\Megatv\CFile::getCropedPath($arFields["UF_IMG_PATH"], array(300, 300), true)
             */
            $path_from = $arFields["UF_IMG_PATH"];
            $path_parts = pathinfo($arFields["UF_IMG_PATH"]);
            $file_name = $path_parts["filename"];
            $path_to = self::$cut_dir. $file_name. "_300_300.jpg";
        
            CFile::add(array(
                "path_from" => $_SERVER["DOCUMENT_ROOT"]. $path_from,
                "path_to" => $_SERVER["DOCUMENT_ROOT"]. $path_to,
                "width" => 300,
                "height" =>  300
            ));
            
            return true;        
        }
        else
        { 
            return implode(', ', $result->getErrors());
        }
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
            'UF_USER_ID' => array(
				'data_type' => 'integer',
                'required'  => true
			),
            'UF_PROG_ID' => array(
				'data_type' => 'integer',
                'required'  => true
			),
            'UF_PROG' => array(
				'data_type' => '\Hawkart\Megatv\ProgTable',
				'reference' => array('=this.UF_PROG_ID' => 'ref.ID'),
			),
            'UF_SCHEDULE_ID' => array(
				'data_type' => 'integer',
			),
            'UF_SCHEDULE' => array(
				'data_type' => '\Hawkart\Megatv\ScheduleTable',
				'reference' => array('=this.UF_SCHEDULE_ID' => 'ref.ID'),
			),
            'UF_CHANNEL_ID' => array(
				'data_type' => 'integer',
			),
            'UF_CHANNEL' => array(
				'data_type' => '\Hawkart\Megatv\ChannelBaseTable',
				'reference' => array('=this.UF_CHANNEL_ID' => 'ref.ID'),
			),
            'UF_PROGRESS_PERS' => array(
				'data_type' => 'integer',
			),
            'UF_PROGRESS_SECS' => array(
				'data_type' => 'integer',
			),
            'UF_DATE_START' => array(
				'data_type' => 'datetime',
                'required'  => true
			),
            'UF_DATE_END' => array(
				'data_type' => 'datetime',
                'required'  => true
			),
            'UF_AFTER_NOTIFY' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			),
            'UF_BEFORE_NOTIFY' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			),
            'UF_WATCHED' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			),
            'UF_SOTAL_ID' => array(
				'data_type' => 'string',
			),
            'UF_URL' => array(
				'data_type' => 'string',
			)
		);
	}
    
    /**
     * Clear table
     */
    public static function deleteAll()
    {
        global $DB;
        $DB->Query("DELETE FROM ".self::getTableName(), false);
        $DB->Query("ALTER TABLE ".self::getTableName()." AUTO_INCREMENT=1", false);
    }
}