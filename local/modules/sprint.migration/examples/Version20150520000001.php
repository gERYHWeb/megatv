<?php

namespace Sprint\Migration;
use \Sprint\Migration\Helpers\IblockHelper;
use \Sprint\Migration\Helpers\EventHelper;
use \Sprint\Migration\Helpers\UserTypeEntityHelper;

class Version20150520000001 extends Version {

    protected $description = "Добавляем инфоблок новости";

    public function up(){
        $helper = new IblockHelper();

        $helper->addIblockTypeIfNotExists(array(
            'ID' => 'content',
            'LANG'=>Array(
                'en'=>Array(
                    'NAME'=>'Контент',
                    'SECTION_NAME'=>'Sections',
                    'ELEMENT_NAME'=>'Elements'
                ),
                'ru'=>Array(
                    'NAME'=>'Контент',
                    'SECTION_NAME'=>'Разделы',
                    'ELEMENT_NAME'=>'Элементы'
                ),
            )
        ));

        $iblockId1 = $helper->addIblockIfNotExists(array(
            'NAME' => 'Новости',
            'CODE' => 'content_news',
            'IBLOCK_TYPE_ID' => 'content',
            'LIST_PAGE_URL' => '',
            'DETAIL_PAGE_URL' => '#SITE_DIR#/news/#ELEMENT_ID#'
        ));

        $helper->addPropertyIfNotExists($iblockId1, array(
            'NAME' => 'Ссылка',
            'CODE' => 'LINK',
        ));

        $this->outSuccess('Инфоблок создан');

    }

    public function down(){
        $helper = new IblockHelper();
        $ok = $helper->deleteIblockIfExists('content_news');

        if ($ok){
            $this->outSuccess('Инфоблок удален');
        } else {
            $this->outError('Ошибка удаления инфоблока');
        }
    }

}
