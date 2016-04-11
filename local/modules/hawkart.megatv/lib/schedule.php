<?php

namespace Hawkart\Megatv;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization;

Localization\Loc::loadMessages(__FILE__);

class ScheduleTable extends Entity\DataManager
{

	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'hw_schedule';
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

        if (isset($data['UF_CODE']))
        {
            $arParams = array("replace_space"=>"-", "replace_other"=>"-");
            $code = \CDev::translit(trim($data["UF_CODE"]), "ru", $arParams);
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
				'title'     => Localization\Loc::getMessage('schedule_entity_active_field'),
				'values'    => array(0, 1),
				'required'  => true
			),
			/*'UF_TITLE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('schedule_entity_title_field'),
                'required'  => true
			),
			'UF_DESC' => array(
				'data_type' => 'text',
				'title'     => Localization\Loc::getMessage('schedule_entity_desc_field'),
			),*/
            'UF_EPG_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('schedule_entity_epg_id_field'),
                'required'  => true
			),
            'UF_CHANNEL_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('schedule_entity_channel_id_field'),
                'required'  => true
			),
            'UF_CHANNEL' => array(
				'data_type' => 'Hawkart\Megatv\Channel',
				'reference' => array('=this.UF_CHANNEL_ID' => 'ref.ID'),
			),
            'UF_PROG_ID' => array(
				'data_type' => 'integer',
				'title'     => Localization\Loc::getMessage('schedule_entity_prog_id_field'),
                'required'  => true
			),
            'UF_PROG' => array(
				'data_type' => 'Hawkart\Megatv\Prog',
				'reference' => array('=this.UF_PROG_ID' => 'ref.ID'),
			),
            'UF_DATE' => array(
				'data_type' => 'date',
				'title'     => Localization\Loc::getMessage('schedule_entity_date_field'),
                'required'  => true
			),
            'UF_DATE_START' => array(
				'data_type' => 'datetime',
				'title'     => Localization\Loc::getMessage('schedule_entity_date_start_field'),
                'required'  => true
			),
            'UF_DATE_END' => array(
				'data_type' => 'datetime',
				'title'     => Localization\Loc::getMessage('schedule_entity_date_end_field'),
                'required'  => true
			),
            'UF_CODE' => array(
				'data_type' => 'string',
				'title'     => Localization\Loc::getMessage('schedule_entity_code_field'),
                'required'  => true
			)
		);
	}
    
    /**
     * Delete schedule before prev day
     */
    public static function deleteOld()
    {
        $result = self::getList(array(
            'filter' => array(
                "<UF_DATE" => new \Bitrix\Main\Type\Date(date('Y-m-d', strtotime('-1 day')), 'Y-m-d')
            ),
            'select' => array("ID")
        ));
        while ($row = $result->fetch())
        {
            ScheduleTable::delete($row["ID"]);
        }
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