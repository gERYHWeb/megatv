<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../');
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$date = date("Y-m-d");

$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
$urls = array();

$count = 1;
$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => array(
        "UF_CHANNEL.UF_ACTIVE" => 1,
        "UF_PROG.UF_ACTIVE" => 1,
    ),
    'select' => array(
        "ID", "UF_ID" => "UF_PROG.UF_EPG_ID", "UF_CHANNEL_CODE" => "UF_CHANNEL.UF_CODE",
    )
));
while ($arSchedule = $result->fetch())
{   
    $url = "http://megatv.su/channels/".$arSchedule["UF_CHANNEL_CODE"]."/".$arSchedule["UF_ID"]."/";
    $urls[] = $url;
}

$urls = array_unique($urls);
foreach($urls as $url)
{
    $xml.="<url>
      <loc>".$url."</loc>
      <lastmod>".$date."</lastmod>
      <changefreq>daily</changefreq>
    </url>";
}
unset($urls);

$xml.="</urlset>";

$file = $_SERVER["DOCUMENT_ROOT"]."/sitemap.xml";
file_put_contents($file, $xml);

die();
?>