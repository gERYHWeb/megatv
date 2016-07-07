<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class SerialTable extends Entity\DataManager
{
    protected static $serials_dir = "/upload/serials/";
    
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_serial';
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
			'UF_TITLE' => array(
				'data_type' => 'string',
                'required'  => true
			),
			'UF_DESC' => array(
				'data_type' => 'text',
				'title'     => Localization\Loc::getMessage('prog_entity_desc_field'),
			),
            'UF_EPG_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_epg_id_field'),
                'required'  => true
			),
            'UF_IMG_ID' => array(
				'data_type' => 'integer'
			),
            'UF_IMG' => array(
				'data_type' => '\Hawkart\Megatv\ImageTable',
				'reference' => array('=this.UF_IMG_ID' => 'ref.ID'),
			),
            'UF_CHANNEL_ID' => array(
				'data_type' => 'string',
                'serialized' => true
			),
            'UF_CHANNEL' => array(
				'data_type' => '\Hawkart\Megatv\ChannelTable',
				'reference' => array('=this.UF_CHANNEL_ID' => 'ref.ID'),
			),
            'UF_EXTERNAL_TITLE' => array(
				'data_type' => 'string'
			),
            'UF_EXTERNAL_URL' => array(
				'data_type' => 'string'
			)
		);
	}
    
    public static function getFilePathBySerial($serial_epg_id)
    {
        return $_SERVER["DOCUMENT_ROOT"].self::$serials_dir.$serial_epg_id.".json";
    }
    
    public static function saveToFile($array, $file)
    {
        file_put_contents($file, json_encode($array));
    }
    
    public static function getListByFile($file)
    {
        $txt = file_get_contents($file);
        $json = json_decode($txt, true);
        
        return $json;
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