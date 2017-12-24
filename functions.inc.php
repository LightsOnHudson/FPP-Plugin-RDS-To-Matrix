<?php


function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

$HEX_OUT ="";
  $offset = 0;
  foreach ($hex as $i => $line)
  {
    $HEX_OUT.= sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']';
    $offset += $width;
  }
return $HEX_OUT;
}

function escapeshellarg_special($file) {
	return "'" . str_replace("'", "'\"'\"'", $file) . "'";
}

//process sequence types

function processSequenceName($sequenceName) {
	logEntry("Sequence name: ".$sequenceName);
	
	$sequenceName = strtoupper($sequenceName);
	
	switch ($sequenceName) {
		
		case "BETABRITE-CLEAR.FSEQ":

		logEntry("Clear BetaBrite Sign");
		$messageToSend="";
		sendLineMessage($messageToSend,$clearMessage=TRUE);
			break;
			exit(0);
			
		default:
			logEntry("We do not support sequence name: ".$sequenceName." at this time");
			
			exit(0);
			
	}
	
}
function processCallback($argv) {

	global $DEBUG,$pluginName;
	
	$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));

	//if($DEBUG)
		//print_r($argv);
	//argv0 = program

	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data

	$registrationType = $argv[2];

	if($DEBUG)
	logEntry("registration type: ".$registrationType);
	
	$data =  $argv[4];

	logEntry("PROCESSING CALLBACK");
	$clearMessage=FALSE;

	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);

				$type = $obj->{'type'};
				
				switch ($type) {
					
					case "sequence":
								
					//$sequenceName = ;
					processSequenceName($obj->{'Sequence'});
					
					break;
					case "media":
					
					logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
					
					$songTitle = $obj->{'title'};
					$songArtist = $obj->{'artist'};
					//	if($songArtist != "") {
				
				
					$messageToSend = $songTitle." ".$SEPARATOR." ".$songArtist;
					if($DEBUG)
					logEntry("MESSAGE to send: ".$messageToSend);
					sendLineMessage($messageToSend,$clearMessage);

				break;
			
				case "both":
					
					if($DEBUG) {
						logEntry("Media File: ".$obj->{'Media'});
						logEntry("Title : ".$obj->{'title'});
						logEntry("Artist: ".$obj->{'artist'});
						
						logEntry("Sequence : ".$obj->{'Sequence'});
						
						
					}
					
					$SONG_TITLE = $obj->{'Media'};
					$SEQUENCE_NAME = $obj->{'Sequence'};
					
					//send to API THINGSPEAK
					//check to see if title / media file etc has something use title first
					if($obj->{'title'} != "") {
						$SONG_TITLE = $obj->{'title'};
					} elseif ($obj->{'Media'} != "") {
						$SONG_TITLE = $obj->{'Media'};
						$SONG_TITLE = basename($SONG_TITLE, ".mp3").PHP_EOL;
					}
					$songArtist = $obj->{'artist'};
				
					$messageToSend = $SONG_TITLE." ".$SEPARATOR." ".$songArtist;
					if($DEBUG)
						logEntry("MESSAGE to send: ".$messageToSend);
					sendLineMessage($messageToSend,$clearMessage);
				
					break;
				
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
						
				}

				
			}
				
			break;
			exit(0);
			
		default:
			exit(0);

	}

}

function logEntry($data) {

	global $logFile,$myPid;

	$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}


//function send the message

function sendLineMessage($line,$clearMessage=FALSE) {

	global $DEBUG,$pluginName,$REMOTE_EDMRDS,$IMMEDIATE_OUTPUT,$settings,$MATRIX_MESSAGE_PLUGIN_NAME,$MATRIX_LOCATION,$MATRIX_EXEC_PAGE_NAME;
	if($DEBUG)
	{
		logEntry("starting sendlinemessage");

		logEntry("Adding to message queue: ".$line);
	}
	addNewMessage($line,$pluginName,$pluginData="PluginData");
	

	if(!$IMMEDIATE_OUTPUT) {
		logEntry("NOT immediately outputting to matrix");
	} else {
		logEntry("IMMEDIATE OUTPUT ENABLED");
		
		//added jan 4 2016
		if($REMOTE_EDMRDS) {
			//remote EDM RDS using the runEventScript remote
			//use the MatrixLocation IP address
			$EDM_RDS_SCRIPT_NAME="rds-song.py";
			$EDM_PLUGIN_NAME="edmrds";
			$REMOTE_EDMRDS_CMD = "/usr/bin/curl -s --basic 'http://".$MATRIX_LOCATION."/runPluginScript.php?pluginName=".$EDM_PLUGIN_NAME."&scriptName=".$EDM_RDS_SCRIPT_NAME."&args=\"-s".htmlspecialchars(urlencode($line))."\"'";
			logEntry("Remote EDM RDS CMD: ".$REMOTE_EDMRDS_CMD);
			exec($REMOTE_EDMRDS_CMD);
			
		} else {
			logEntry ( "IMMEDIATE OUTPUT ENABLED" );
			
			// write high water mark, so that if run-matrix is run it will not re-run old messages
			
			$pluginLatest = time ();
			
			// logEntry("message queue latest: ".$pluginLatest);
			// logEntry("Writing high water mark for plugin: ".$pluginName." LAST_READ = ".$pluginLatest);
			
			// file_put_contents($messageQueuePluginPath.$pluginSubscriptions[$pluginIndex].".lastRead",$pluginLatest);
			// WriteSettingToFile("LAST_READ",urlencode($pluginLatest),$pluginName);
			
			// do{
			
			logEntry ( "Matrix location: " . $MATRIX_LOCATION );
			logEntry ( "Matrix Exec page: " . $MATRIX_EXEC_PAGE_NAME );
			$MATRIX_ACTIVE = true;
			WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );
			logEntry ( "MATRIX ACTIVE: " . $MATRIX_ACTIVE );
			
			$curlURL = "http://" . $MATRIX_LOCATION . "/plugin.php?plugin=" . $MATRIX_MESSAGE_PLUGIN_NAME . "&page=" . $MATRIX_EXEC_PAGE_NAME . "&nopage=1&subscribedPlugin=" . $pluginName . "&onDemandMessage=" . urlencode ( $messageText );
			if ($DEBUG)
				logEntry ( "MATRIX TRIGGER: " . $curlURL );
				
				$ch = curl_init ();
				curl_setopt ( $ch, CURLOPT_URL, $curlURL );
				
				curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt ( $ch, CURLOPT_WRITEFUNCTION, 'do_nothing' );
				curl_setopt ( $ch, CURLOPT_VERBOSE, false );
				
				$result = curl_exec ( $ch );
				logEntry ( "Curl result: " . $result ); // $result;
				curl_close ( $ch );
				
				$MATRIX_ACTIVE = false;
				WriteSettingToFile ( "MATRIX_ACTIVE", urlencode ( $MATRIX_ACTIVE ), $pluginName );
		
	}
	}
	
	if($DEBUG)
		logEntry("Leaving SendLine Message");
}



//create script to randmomize
function createRandomizerScript() {


	global $radioStationRepeatScriptFile,$pluginName,$randomizerScript;


	logEntry("Creating Randomizer script: ".$radioStationRepeatScriptFile);

	$data = "";
	$data  = "#!/bin/sh\n";
	$data .= "\n";
	$data .= "#Script to run randomizer\n";
	$data .= "#Created by ".$pluginName."\n";
	$data .= "#\n";
	$data .= "/usr/bin/php ".$randomizerScript."\n";


	$fs = fopen($radioStationRepeatScriptFile,"w");
	fputs($fs, $data);
	fclose($fs);

}

//crate the event file
function createRandomizerEventFile() {

	global $radioStationRepeatScriptFile,$pluginName,$randomizerScript,$radioStationRandomizerEventFile,$MAJOR,$MINOR,$radioStationRadomizerEventName;


	logEntry("Creating Randomizer event file: ".$radioStationRandomizerEventFile);

	$data = "";
	$data .= "majorID=".$MAJOR."\n";
	$data .= "minorID=".$MINOR."\n";

	$data .= "name='".$radioStationRadomizerEventName."'\n";

	$data .= "effect=''\n";
	$data .="startChannel=\n";
	$data .= "script='".pathinfo($radioStationRepeatScriptFile,PATHINFO_BASENAME)."'\n";



	$fs = fopen($radioStationRandomizerEventFile,"w");
	fputs($fs, $data);
	fclose($fs);
}
?>
