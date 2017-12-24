<?php
//$DEBUG=true;
include_once "/opt/fpp/www/common.php";

//added remote edmrds option
//need to soft link /rds-song.py to /home/fpp/media/scripts from home/fpp/media/plugins/edmrds


$pluginName = "rdsToMatrix";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";

include_once 'version.inc';

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$myPid = getmypid();


$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;


$logFile = $settings['logDirectory']."/".$pluginName.".log";



$messageQueuePluginPath = $settings['pluginDirectory']."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
{
	include $messageQueuePluginPath."functions.inc.php";
	$MESSAGE_QUEUE_PLUGIN_ENABLED=true;
	
} else {
	logEntry("Message Queue Plugin not installed, some features will be disabled");
}


$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-RDS-To-Matrix.git";

logEntry("plugin update file: ".$pluginUpdateFile);

//$DEBUG = false;







//createBetaBriteSequenceFiles();

if(isset($_POST['submit']))
{
	
	$STATION_ID=trim($_POST["STATION_ID"]);
	
	$STATIC_TEXT_POST=trim($_POST["STATIC_TEXT_POST"]);
	$STATIC_TEXT_PRE=trim($_POST["STATIC_TEXT_PRE"]);
	$ENABLED=$_POST["ENABLED"];
	$IMMEDIATE_OUTPUT=$_POST["IMMEDIATE_OUTPUT"];
	$MATRIX_LOCATION=$_POST["MATRIX_LOCATION"];
	$REMOTE_EDMRDS=$_POST["REMOTE_EDMRDS"];
	
	$SEPARATOR = $_POST["SEPARATOR"];

//	echo "Writring config fie <br/> \n";


	WriteSettingToFile("STATIC_TEXT_PRE",urlencode($STATIC_TEXT_PRE),$pluginName);
	WriteSettingToFile("STATIC_TEXT_POST",urlencode($STATIC_TEXT_POST),$pluginName);
//	WriteSettingToFile("ENABLED",$ENABLED,$pluginName);
	//WriteSettingToFile("IMMEDIATE_OUTPUT",$IMMEDIATE_OUTPUT,$pluginName);
	WriteSettingToFile("STATION_ID",urlencode($STATION_ID),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($SEPARATOR),$pluginName);
	WriteSettingToFile("MATRIX_LOCATION",urlencode($MATRIX_LOCATION),$pluginName);
	WriteSettingToFile("REMOTE_EDMRDS",$REMOTE_EDMRDS,$pluginName);
} 
sleep(1);
$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	$DEBUG = urldecode($pluginSettings['DEBUG']);
	
	if($DEBUG) {
		logEntry("Plugin Config file: ".$pluginConfigFile);
	}
	
	$STATION_ID = urldecode($pluginSettings['STATION_ID']);
	
	$STATIC_TEXT_PRE = urldecode($pluginSettings['STATIC_TEXT_PRE']);
	$STATIC_TEXT_POST = urldecode($pluginSettings['STATIC_TEXT_POST']);
	$ENABLED = $pluginSettings['ENABLED'];
	$IMMEDIATE_OUTPUT = $pluginSettings['IMMEDIATE_OUTPUT'];
	$MATRIX_LOCATION = $pluginSettings['MATRIX_LOCATION'];
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	$REMOTE_EDMRDS = urldecode($pluginSettings['REMOTE_EDMRDS']);
	
if($DEBUG) {
	echo "POST Variables: <br/> <pre> \n";
	print_r($_POST);
	echo "</pre> \n";
	echo "<p/> \n";
}

if(isset($_POST['updatePlugin']))
{
	logEntry("updating plugin...");
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}

$Plugin_DBName = $settings['configDirectory']."/FPP.".$pluginName.".db";

//echo "PLUGIN DB:NAME: ".$Plugin_DBName;

$db = new SQLite3($Plugin_DBName) or die('Unable to open database');
createTables();

?>

<html>
<head>
</head>

<div id="rdsToMatrix" class="settings">
<fieldset>
<legend><?php echo $pluginName." Version: ".$pluginVersion;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None known</ul>

<p>Configuration:
<ul>
<li>Configure your Static text you want to send in front of Artist and song and post text, loop time if you want looping and color</li>
<li>If using a Remote EDMRDS instance. follow the README_REMOTE_EDMRDS.txt in the plugin folder</li>
</ul>

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<? echo $pluginName;?>&page=plugin_setup.php">
<?php 
echo "ENABLE PLUGIN: ";

PrintSettingCheckbox("RDS To Matrix", "ENABLED", $restart = 1, $reboot = 0, "true", "", $pluginName = $pluginName, $callbackName = "");



echo "<p/> \n";
echo "Immediately output to Matrix (Run MATRIX plugin): ";

PrintSettingCheckbox("Immediate Output to matrix", "IMMEDIATE_OUTPUT", $restart = 0, $reboot = 0, "true", "", $pluginName = $pluginName, $callbackName = "");



echo "<p/> \n";
?>

Manually Set Station ID<br>
<p><label for="STATION_ID">Station ID:</label>
<input type="text" value="<? if($STATION_ID !="" ) { echo $STATION_ID; } else { echo "";};?>" name="STATION_ID" id="STATION_ID"></input>
(Expected format: up to 8 characters)
</p>




<p/>

STATIC TEXT PRE:
<input type="text" size="64" value="<? if($STATIC_TEXT_PRE !="" ) { echo $STATIC_TEXT_PRE; } else { echo "";}?>" name="STATIC_TEXT_PRE" id="STATIC_TEXT_PRE"></input>


<p/>

STATIC TEXT POST:
<input type="text" size="64" value="<? if($STATIC_TEXT_POST !="" ) { echo $STATIC_TEXT_POST; } else { echo "";}?>" name="STATIC_TEXT_POST" id="STATIC_TEXT_POST"></input>

<p/>

Separator between SongTitle & Song Artist:
<input type="text" value="<? if($SEPARATOR !="" ) { echo $SEPARATOR; } else { echo "-";}?>" name="SEPARATOR" id="SEPARATOR"></input>

<p/>
<?
echo "SEND TO REMOTE EDM RDS: ";

if($REMOTE_EDMRDS == "on" || $REMOTE_EDMRDS == 1) {
		echo "<input type=\"checkbox\" checked name=\"REMOTE_EDMRDS\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"REMOTE_EDMRDS\"> \n";
}


?>
<p/>


MATRIX Message Plugin/Remote EDMRDS FPP Instance Location: (IP Address. default 127.0.0.1);
<input type="text" size="15" value="<? if($MATRIX_LOCATION !="" ) { echo $MATRIX_LOCATION; } else { echo "127.0.0.1";}?>" name="MATRIX_LOCATION" id="MATRIX_LOCATION"></input>
<p/>

<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">


<p>To report a bug, please file it against the BetaBrite plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-BetaBrite

<p>
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}

echo "<p/> \n";
echo "DEBUG: ";

//if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
//	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
PrintSettingCheckbox("DEBUG", "DEBUG", $restart = 0, $reboot = 0, "true", "", $pluginName = $pluginName, $callbackName = "");

echo "<p/> \n";
?>
<p>To report a bug, please file it against <?php echo $gitURL;?>
</form>

</fieldset>
</div>
<br />
</html>
