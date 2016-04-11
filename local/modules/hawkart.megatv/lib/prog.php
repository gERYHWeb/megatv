<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ProgTable extends Entity\DataManager
{

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_prog';
	}
    
    /**
     * Change data before adding
     * 
     * @return object 
     */
    public static function onBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter("fields");

        if (isset($data['UF_TITLE']))
        {
            $arParams = array("replace_space"=>"-", "replace_other"=>"-");
            $code = \CDev::translit(trim($data["UF_TITLE"]), "ru", $arParams);
            $result->modifyFields(array('UF_CODE' => $code));
        }

        return $result;
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
				'title'     => Localization\Loc::getMessage('prog_entity_active_field'),
				'values'    => array(0, 1),
				'required'  => true
			),
			'UF_TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_title_field'),
                'required'  => true
			),
            'UF_SUB_TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_sub_title_field')
			),
			'UF_DESC' => array(
				'data_type' => 'text',
				'title'     => Localization\Loc::getMessage('prog_entity_desc_field'),
			),
            'UF_SUB_DESC' => array(
				'data_type' => 'text',
				'title'     => Localization\Loc::getMessage('prog_entity_sub_desc_field'),
			),
            'UF_EPG_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_epg_id_field'),
                'required'  => true
			),
            'UF_EPG_SUB_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_epg_sub_id_field')
			),
            'UF_SEASON' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_season_field'),
			),
            'UF_SERIA' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_seria_field'),
			),
            'UF_CATEGORY' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_category_field'),
			),
            'UF_DIRECTOR' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_director_field'),
			),
            'UF_ACTOR' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_actor_field'),
			),
            'UF_COUNTRY' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_country_field'),
			),
            'UF_TOPIC' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_topic_field'),
			),
            'UF_PRESENTER' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_presenter_field'),
			),
            'UF_GANRE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_ganre_field'),
			),
            'UF_YEAR_LIMIT' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('prog_entity_year_limit_field'),
			),
            'UF_YEAR' => array(
				'data_type' => 'string',
				//'title'     => Localization\Loc::getMessage('prog_entity_year_field'),
			),
            'UF_RATING' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('prog_entity_rating_field'),
			),
            'UF_HD' => array(
				'data_type' => 'boolean',
				'title'     => Localization\Loc::getMessage('prog_entity_hd_field'),
				'values'    => array(0, 1),
			), 
            'UF_PREMIERE' => array(
				'data_type' => 'boolean',
				'values'    => array(0, 1),
			),             
            'UF_CODE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('prog_entity_code_field'),
                'required'  => true
			),
            'UF_IMG_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('prog_entity_img_id_field')
			),
            'UF_IMG' => array(
				'data_type' => '\Hawkart\Megatv\Image',
				'reference' => array('=this.UF_IMG_ID' => 'ref.ID'),
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