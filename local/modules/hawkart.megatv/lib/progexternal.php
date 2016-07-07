<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ProgExternalTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_prog_external';
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
            'UF_JSON' => array(
				'data_type' => 'text',
				'serialized' => true
			),
            'UF_SERIAL_ID' => array(
				'data_type' => 'integer',
                'required'  => true
			),
            'UF_SERIAL' => array(
				'data_type' => '\Hawkart\Megatv\SerialTable',
				'reference' => array('=this.UF_SERIAL_ID' => 'ref.ID'),
			),
            'UF_EXTERNAL_ID' =>array(
				'data_type' => 'string',
			),
            'UF_VIDEO_URL' =>array(
				'data_type' => 'string',
			),
            'UF_THUMBNAIL_URL' => array(
				'data_type' => 'string'
			),
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