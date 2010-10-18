<?php

function dbQuery($sql) 
	{
        $res = mysql_query($sql); //or $errormsg='Error: '. mysql_error();
        return $res;    
	}

function isempty($var) 
	{
    	if (((is_null($var) || rtrim($var) == "") && $var !== false) || (is_array($var) && empty($var))) {
	        return true;
	    } else {
	        return false;
    	}
	}

function cleaninput($inputstring)
	{
		if (($inputstring==null) || ($inputstring==""))
		{
			return false;
		}
		$output = mysql_real_escape_string(strip_tags(trim($inputstring)));
		
		return $output;
	}
	
function mysql_to_human($mydate, $showtime=false)
	{
		if (isempty($mydate) || $mydate == "0000-00-00 00:00:00") {
			return "Date Not Set";
		} else {
			$human = date("m/d/Y",strtotime($mydate));
			
			if ($showtime) {
				$human .= " " . date("G:i:s T",strtotime($mydate));
			}
			
			return $human;
		}
	}

function mysql_to_vcard($mydate)
	{
		if (isempty($mydate) || $mydate == "0000-00-00 00:00:00") {
			$curDate= date('Ymd\THis\Z');
			return $curDate;
		} else {
			$vdate = date("Ymd\THis\Z",strtotime($mydate));
			return $vdate;
		}
	}
	
function rsvpHeader()
	{
		wp_enqueue_script('prototype');
		wp_enqueue_script('scriptaculous');
		wp_enqueue_script('tablekit','/wp-includes/js/tablekit.js','prototype','1.5');
		wp_enqueue_script('validation','/wp-includes/js/validation.js','prototype','1.5');
	}



?>
