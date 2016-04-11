<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ImageTable extends Entity\DataManager
{

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_image';
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
			'UF_EXTERNAL_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('image_entity_external_id_field'),
                'required'  => true,
			),
            'UF_PATH' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('image_entity_path_field'),
                'required'  => true
			),
            'UF_WIDTH' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('image_entity_width_field'),
			),
            'UF_HEIGHT' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('image_entity_height_field')
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