#!/usr/bin/php
<?
error_reporting(0);

$pluginName ="rdsToMatrix";
//$DEBUG=true;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");

include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("commonFunctions.inc.php");

$MATRIX_MESSAGE_PLUGIN_NAME = "MatrixMessage";
//page name to run the matrix code to output to matrix (remote or local);
$MATRIX_EXEC_PAGE_NAME = "matrix.php";


$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$messageQueuePluginPath = $settings['pluginDirectory']."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
        {
                include $messageQueuePluginPath."functions.inc.php";
                $MESSAGE_QUEUE_PLUGIN_ENABLED=true;

        } else {
                logEntry("Message Queue Plugin not installed, some features will be disabled");
        }

$ENABLED="";

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;

if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);
	
$ENABLED = urldecode($pluginSettings['ENABLED']);
$IMMEDIATE_OUTPUT = urldecode($pluginSettings['IMMEDIATE_OUTPUT']);
$MATRIX_LOCATION = urldecode($pluginSettings['MATRIX_LOCATION']);
$REMOTE_EDMRDS = urldecode($pluginSettings['REMOTE_EDMRDS']);

//arg0 is  the program
//arg1 is the first argument in the registration this will be --list

$logFile = $settings['logDirectory']."/".$pluginName.".log";
//$logFile = $logDirectory."/logs/betabrite.log";
//echo "Enabled: ".$ENABLED."<br/> \n";


if($ENABLED != "on" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	
	exit(0);
}
$callbackRegisters = "media\n";
$myPid = getmypid();
//var_dump($argv);


$FPPD_COMMAND = $argv[1];

//echo "FPPD Command: ".$FPPD_COMMAND."<br/> \n";

if($FPPD_COMMAND == "--list") {

			echo $callbackRegisters;
			logEntry("FPPD List Registration request: responded:". $callbackRegisters);
			exit(0);
}

if($FPPD_COMMAND == "--type") {
		if($DEBUG)
			logEntry("DEBUG: type callback requested");
			//we got a register request message from the daemon
		$forkResult = fork($argv);
		if($DEBUG)
		logEntry("DEBUG: Fork Result: ".$forkResult);
		exit(0); 
		//	processCallback($argv);	
} else {

			logEntry($argv[0]." called with no parameteres");
			exit(0);
}
?>
