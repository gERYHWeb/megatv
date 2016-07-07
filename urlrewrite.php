<?
$arUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^/services/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	"/services/index.php",
	),
    array( 
        "CONDITION" => "#^/channels/#", 
        "ID" => "hawkart:channel.catalog", 
        "PATH" => "/channels/index.php",
    ),
    array(
		"CONDITION"	=>	"#^/serials/(.*)/(.*)#",
		"RULE"	=>	"EPG_ID=\$1",
		"ID"	=>	"hawkart:serial.detail",
		"PATH"	=>	"/serials/detail.php",
	),
    array(
		"CONDITION"	=>	"#^/serials/(.*)/#",
		"RULE"	=>	"EPG_ID=\$1",
		"ID"	=>	"hawkart:serial.detail",
		"PATH"	=>	"/serials/detail.php",
	),
    array(
		"CONDITION"	=>	"#^/serials/(.*)#",
		"RULE"	=>	"",
		"ID"	=>	"hawkart:serial.list",
		"PATH"	=>	"/serials/index.php",
	),
);

?>