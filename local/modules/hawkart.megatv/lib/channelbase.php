<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ChannelBaseTable extends Entity\DataManager
{
    /**
	 * Set primary key from 1
	 *
	 * @return string
	 */
    public static function updatePrimary()
    {
        return "ALTER TABLE hw_channel_base AUTO_INCREMENT=1";
    }

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_channel_base';
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
     * Если включили бесплатный канал, активируем для всех пользователей подписку.
     * 
     * @return object 
     */
    public static function OnBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $primary = $event->getParameter("id");
        $data = $event->getParameter("fields");

        $res = self::getById($primary);
        $arChannel = $res->fetch();
        $price = floatval($arChannel["UF_PRICE_H24"]);

        if($data["UF_ACTIVE"] && !$arChannel["UF_ACTIVE"] && $price==0)
        {
            //Найдем пользователей, для кого эта подписка была включена
            $userIds = array();
            
            $result = SubscribeTable::getList(array(
                'filter' => array("=UF_CHANNEL_ID" => $data["ID"]),
                'select' => array("ID", "UF_USER_ID")
            ));
            while ($arSub = $result->fetch())
            {
                $userIds[$arSub["UF_USER_ID"]] = $arSub["ID"];
            }

            $CSubscribe = new CSubscribe("CHANNEL");
            $dbUsers = \CUser::GetList(($by="EMAIL"), ($order="desc"), Array("ACTIVE" =>"Y"));
            while($arUser = $dbUsers->Fetch())
            {
                if(!array_key_exists($arUser["ID"], $userIds))
                {
                    $CSubscribe->setUserSubscribe($data["ID"], $arUser["ID"]);
                }else{
                    $sub_id = $userIds[$arUser["ID"]];
                    $CSubscribe->updateUserSubscribe($sub_id, array("UF_ACTIVE"=>1));
                }
            }
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
            'UF_SORT' => array(
				'data_type' => 'integer',
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
            'UF_YOUTUBE' => array(
				'data_type' => 'string',
			),
            'UF_H1' => array(
				'data_type' => 'string',
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