<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ChannelTable extends Entity\DataManager
{
    /**
	 * Set primary key from 1
	 *
	 * @return string
	 */
    public static function updatePrimary()
    {
        return "ALTER TABLE hw_channel AUTO_INCREMENT=1";
    }

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_channel';
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
				'title'     => Localization\Loc::getMessage('channel_entity_active_field'),
				'values'    => array(0, 1),
				'required'  => true
			),
			'UF_TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_title_field'),
                'required'  => true
			),
			'UF_DESC' => array(
				'data_type' => 'text',
				'title'     => Localization\Loc::getMessage('channel_entity_desc_field'),
			),
            'UF_EPG_ID' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_epg_id_field'),
                'required'  => true
			),
            'UF_ICON' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_icon_field'),
			),
            'UF_SITE_URL' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_site_url_field'),
			),
            'UF_IS_NEWS' => array(
				'data_type' => 'boolean',
				'title'     => Localization\Loc::getMessage('channel_entity_is_news_field'),
				'values'    => array(0, 1),
			),
			'UF_PRICE_H24' => array(
				'data_type' => 'float',
				'title'     => Localization\Loc::getMessage('channel_entity_price_h24_field')
			),
            'UF_PRICE_M' => array(
				'data_type' => 'float',
			),
            'UF_FORBID_REC' => array(
				'data_type' => 'boolean',
				'title'     => Localization\Loc::getMessage('channel_entity_forbid_rec_field'),
				'values'    => array(0, 1),
			),
            'UF_FRAME_URL' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_frame_url_field'),
			),
            'UF_STREAM_URL' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_stream_url_field'),
			),
            'UF_CODE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('channel_entity_code_field'),
                'required'  => true
			),
            'UF_IMG_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('channel_entity_img_id_field')
			),
            'UF_IMG' => array(
				'data_type' => 'Local\Hawkart\Megatv\Image',
				'reference' => array('=this.IMG_ID' => 'ref.ID'),
			),
			'UF_SORT' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('channel_entity_sort_field'),
			),
		);
	}
}