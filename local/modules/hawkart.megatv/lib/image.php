<?php

//namespace Hawkart\Megatv;

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
			'TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('image_entity_title_field'),
                'required'  => true
			),
            'PATH' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('image_entity_path_field'),
                'required'  => true
			),
            'WIDTH' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('image_entity_width_field'),
			),
            'HEIGHT' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('image_entity_height_field')
			),
            'ORIGIN_TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('image_entity_origin_title_field'),
			),
		);
	}
}