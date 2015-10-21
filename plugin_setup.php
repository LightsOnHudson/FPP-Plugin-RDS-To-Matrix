<?php
//$DEBUG=true;
//include_once "/opt/fpp/www/common.php";

$pluginName = "rdsToMatrix";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";


$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$myPid = getmypid();

$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-RDS-To-Matrix.git";

logEntry("plugin update file: ".$pluginUpdateFile);

$DEBUG = false;


$STATION_ID=trim($_POST["STATION_ID"]);

$STATIC_TEXT_POST=trim($_POST["STATIC_TEXT_POST"]);
$STATIC_TEXT_PRE=trim($_POST["STATIC_TEXT_PRE"]);
$ENABLED=$_POST["ENABLED"];
$IMMEDIATE_OUTPUT=$_POST["IMMEDIATE_OUTPUT"];

$SEPARATOR = $_POST["SEPARATOR"];

if($DEBUG) {

	echo "PORT: ".$_POST['PORT'];//print_r($_POST["PORT"]);
	echo "loop message: ".$_POST["LOOPMESSAGE"]."<br/> \n";
	
}



//createBetaBriteSequenceFiles();

if(isset($_POST['submit']))
{

//	echo "Writring config fie <br/> \n";


	WriteSettingToFile("STATIC_TEXT_PRE",urlencode($STATIC_TEXT_PRE),$pluginName);
	WriteSettingToFile("STATIC_TEXT_POST",urlencode($STATIC_TEXT_POST),$pluginName);
	WriteSettingToFile("ENABLED",$ENABLED,$pluginName);
	WriteSettingToFile("IMMEDIATE_OUTPUT",$IMMEDIATE_OUTPUT,$pluginName);
	WriteSettingToFile("STATION_ID",urlencode($STATION_ID),$pluginName);
	WriteSettingToFile("SEPARATOR",urlencode($SEPARATOR),$pluginName);

} else {

	
	$STATION_ID = $pluginSettings['STATION_ID'];
	
	$STATIC_TEXT_PRE = urldecode($pluginSettings['STATIC_TEXT_PRE']);
	$STATIC_TEXT_POST = urldecode($pluginSettings['STATIC_TEXT_POST']);
	$ENABLED = $pluginSettings['ENABLED'];
	$IMMEDIATE_OUTPUT = $pluginSettings['IMMEDIATE_OUTPUT'];
	
	$SEPARATOR = urldecode($pluginSettings['SEPARATOR']);
	
	
}

if(isset($_POST['updatePlugin']))
{
	logEntry("updating plugin...");
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}

?>

<html>
<head>
</head>

<div id="rdsToMatrix" class="settings">
<fieldset>
<legend>RDS To Matrix Support Instructions</legend>

<p>Known Issues:
<ul>
<li>None known</ul>

<p>Configuration:
<ul>
<li>Configure your Static text you want to send in front of Artist and song and post text, loop time if you want looping and color</li>
</ul>

<form method="post" action="http://<? echo $_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<? echo $pluginName;?>&page=plugin_setup.php">
<?php 
echo "ENABLE PLUGIN: ";

if($ENABLED == "on" || $ENABLED == 1) {
		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
	} else {
		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}


echo "<p/> \n";
echo "Immediately output to Matrix (Run MATRIX plugin): ";

if($IMMEDIATE_OUTPUT == "on" || $IMMEDIATE_OUTPUT == 1) {
	echo "<input type=\"checkbox\" checked name=\"IMMEDIATE_OUTPUT\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
} else {
	echo "<input type=\"checkbox\"  name=\"IMMEDIATE_OUTPUT\"> \n";
}


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
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">


<p>To report a bug, please file it against the BetaBrite plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-BetaBrite

<p>
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
<p>To report a bug, please file it against <?php echo $gitURL;?>
</form>

</fieldset>
</div>
<br />
</html>
