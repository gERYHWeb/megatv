<?php
namespace Hawkart\Megatv;

use Bitrix\Main\Text\Encoding;

class GeoCity extends IPGeoBase
{
    /** @var IPGeoBase */
    public static $instance;

    /** @var array */
    protected static $cacheIp = array();
    
    public static function download()
    {
        $url = "http://ipgeobase.ru/files/db/Main/geo_files.zip";
        $zipFile = $_SERVER["DOCUMENT_ROOT"]."/local/modules/hawkart.megatv/data/ipgeobase.zip";
        $zipResource = fopen($zipFile, "w");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $page = curl_exec($ch);
        curl_close($ch);
        
        $zip = new \ZipArchive;
        $extractPath = $_SERVER["DOCUMENT_ROOT"]."/local/modules/hawkart.megatv/data/";
        if($zip->open($zipFile) != "true")
        {
            echo "Error :- Unable to open the Zip File";
        } 
        /* Extract Zip File */
        $zip->extractTo($extractPath);
        $zip->close();
        
        unlink($zipFile);
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $dataDir = dirname(__DIR__);
            self::$instance = new self($dataDir . '/data/cidr_optim.txt', $dataDir . '/data/cities.txt');
        }

        return self::$instance;
    }

    /**
     * @param string $ip
     * @return mixed
     */
    public function getRecord($ip = null)
    {
        if($ip === null) {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }
        
        if (!isset(self::$cacheIp[$ip])) {
            $result = parent::getRecord($ip);

            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    $result[$key] = Encoding::convertEncoding($value, 'windows-1251', SITE_CHARSET);
                }
            }

            self::$cacheIp[$ip] = $result;
        }

        return self::$cacheIp[$ip];
    }
    
    /**
     * @param array $arCity
     * @return array
     */
    public function getIpByCities($arCity)
    {
        $arCityIp = array();
        
        $dataDir = dirname(__DIR__);
        $CIDRFile = $dataDir . '/data/cidr_optim.txt';
        $CitiesFile = $dataDir . '/data/cities.txt';
        
        $this->fhandleCIDR = fopen($CIDRFile, 'r') or die("Cannot open $CIDRFile");
        $this->fhandleCities = fopen($CitiesFile, 'r') or die("Cannot open $CitiesFile");

        $city_ids = array();
        
        rewind($this->fhandleCities);
        while(!feof($this->fhandleCities))
        {
            $str = fgets($this->fhandleCities);
            $arRecord = explode("\t", trim($str));

            $city_name = Encoding::convertEncoding($arRecord[1], 'windows-1251', SITE_CHARSET);

            if(intval($arCity[$city_name])>0)
            {
                $city_ids[$arRecord[0]] = $city_name;
            }
        }
        
        $city_ids[1753] = "Магас";
        
        if(count($city_ids)>0)
        {
            rewind($this->fhandleCIDR);
            while(!feof($this->fhandleCIDR))
            {
                $str = fgets($this->fhandleCIDR);
                $arRecord = explode("\t", trim($str));
                
                if(!empty($city_ids[$arRecord[4]]))
                {
                    $city_name = $city_ids[$arRecord[4]];
                    $city_site_id = $arCity[$city_name];
                    $arCityIp[$city_site_id] = $arRecord[2];
                }
            }
        }
        
        unset($arCity);
        unset($city_ids);
        
        return $arCityIp;      
    }
}