<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class CountryTable extends Entity\DataManager
{
    protected static $rus_id = 15;
    
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_country';
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
            'UF_ACTIVE' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			),
			'UF_TITLE' => array(
				'data_type' => 'string',
                'required'  => true
			),
            'UF_EPG_ID' => array(
				'data_type' => 'string'
			),
            'UF_ISO' => array(
				'data_type' => 'string'
			),
            'UF_EXIST' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1)
			)
		);
	}
}