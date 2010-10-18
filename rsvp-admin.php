<?php
require_once( dirname (__FILE__) . '/rsvp-common.php');

global $wpdb;
global $userdata;
get_currentuserinfo();
global $table_name;
	$table_name = get_option( 'rsvp_db_tablename' );
global $meal_table;
	$meal_table = get_option( 'rsvp_db_menu' );

global $errormsg, $title, $first, $last, $namesuffix, $address, $city, $state,$zip,$phone,$partycount,$priority,$code;

	
function print_guestlist() {
	global $wpdb;
	global $table_name;

	$totalentries = $wpdb->get_var("SELECT COUNT(*) as count FROM $table_name");
	$totalinvites = $wpdb->get_var("SELECT SUM(partycount) as sum FROM $table_name");
	$totalaccepted = $wpdb->get_var("SELECT SUM(partycountattending) as sum FROM $table_name");
	$output = "Total Entries:  <strong>$totalentries</strong><br />\n";
	$output .= "Total Invited Guests:  <strong>$totalinvites</strong><br />\n";
	$output .= "Total Invited Guests Attending: <strong>$totalaccepted</strong><br />\n";
	$output .= "<table style=\"font-size:inherit;width:100%;margin:0px;\" id=\"rsvp-guestlist-table\">";
	$output .= "<thead><th align=left>Name</th><th align=left>Address</th><th align=left>Party Count</th><th>Attending</th><th></th><th></th><th></th><th></th></thead><tbody>";
	$filter = cleaninput($_GET['s']);
	$sort = cleaninput($_GET['sort']);
	switch ($sort) {
		case "newest":
			$orderby = "dateadded DESC";
			break;
		case "lastupdated":
			$orderby = "dateupdated DESC";
			break;
		case "alpharev":
			$orderby = "lastname DESC,firstname ASC";
			break;
		case "attending":
			$orderby = "attendingwedding DESC";
			break;
		case "notattending":
			$orderby = "attendingwedding";
			break;			
		default:
			$orderby = "lastname,firstname";
			break;
	}

	if (!isempty(trim($filter))) {
		$where = "WHERE lastname LIKE '$filter%' OR firstname LIKE '$filter%'";
		}
	$sql = "SELECT * FROM $table_name $where ORDER BY $orderby";
	$listres = $wpdb->get_results($sql);
	
	 foreach($listres as $listres) {
		if ($listres->address == "" || $listres->phone == "" || $listres->city == "" || $listres->zip == "") {
			$trcolor = "#fda";
			$titletag = "Needs More Data";
			$eek = "<img src=\"/wp-includes/images/smilies/icon_exclaim.gif\" style=\"border:0px;height:11px;vertical-align:middle;\" alt=\"eek! \" />";
		} else {
			$trcolor= "inherit";
			$titletag = "Phone: $listres->phone";
			$eek = "";
		}
			
		$output .= "<tr title=\"$titletag\" style=\"background-color:$trcolor\" >"; //onmouseover=\"$('$listres->id-floater').setStyle({visibility:'visible'});\" onmouseout=\"$('$listres->id-floater').setStyle({visibility:'hidden'});\" >";
		if (! $listres->namesuffix == "" && ! $listres->namesuffix == null)
			$tempsuffix = " $listres->namesuffix";
		else
			$tempsuffix = "";
		$output .= "<td><div id=\"$listres->id-floater\" class=\"floater\" ><span style=\"font-size:110%;font-weight:bold;color:#553;\">$listres->title $listres->firstname $listres->lastname $listres->namesuffix</span><br /><em>Address:</em><br />$listres->address<br />$listres->city, $listres->state $listres->zip<br /><em>Phone:</em><br />$listres->phone<br /><em>Code Word:</em><br/>$listres->code</div>";
		$output .= "$listres->lastname$tempsuffix, $listres->title $listres->firstname</td>";
		$output .= "<td style=\"font-size:90%;\">$eek $listres->city, $listres->state</td>";
		$priorityIMG = "";
		switch ($listres->priority) {
			case 0: $priorityIMG = "<img src=\"/wp-includes/images/smilies/icon_mrgreen.gif\" style=\"border:0px;height:11px;vertical-align:middle;\" title=\"Definitely\" alt=\"Definitely\" />";
			break;
			case 1: $priorityIMG = "<img src=\"/wp-includes/images/smilies/icon_neutral.gif\" style=\"border:0px;height:11px;vertical-align:middle;\" title=\"Maybe\" alt=\"Maybe\" />";
			break;
			case 2: $priorityIMG = "<img src=\"/wp-includes/images/smilies/icon_cry.gif\" style=\"border:0px;height:11px;vertical-align:middle;\" title=\"Maybe Maybe\" alt=\"Maybe Maybe\" />";
			break;

		}
		$attwed = $listres->attendingwedding == 1 ? 'checked': '' ;
		$attrec = $listres->attendingreception == 1 ? 'checked': '' ;
		$output .= "<td>$listres->partycount</td><td>$listres->partycountattending<br /><input type=\"checkbox\" disabled=\"disabled\" $attwed />Wedding<br /><input type=\"checkbox\" disabled=\"disabled\" $attrec />Reception</td><td>$priorityIMG</td>";
			if ($listres->userupdated == "" or $listres->userupdated == null)
				$userupdated = "n/a";
			else
				$userupdated = $listres->userupdated;
			$useradded = $listres->useradded;
		if ( $listres->address != "" && $listres->phone != "" && $listres->city != "" && $listres->zip != "") {
               		$output .= "<td><a href=\"/wp-content/plugins/rsvp/rsvp-ajax.php?p=getvcard&id=$listres->id\" title=\"vcard\"><img src=\"../wp-content/plugins/rsvp/rsvp-vcard.png\" style=\"border:none;height:10px;\" /></a></td>";
		} else {
			$output .= "<td></td>";
		}

		$output .= "<td><a href=\"admin.php?page=rsvp_add_attendees&p=edit&id=$listres->id\" title=\"edit $listres->id \n[Created By: $useradded (" . mysql_to_human($listres->dateadded,true) . ")] \n[Edited By: $userupdated (" . mysql_to_human($listres->dateupdated,true) . ")] \" style=\"border:none;\"><img style=\"border:none;height:10px;\" src=\"/wp-includes/images/smilies/icon_idea.gif\"> edit</a></td>";
			if ( current_user_can('manage_options') ) {
				$output .= "<td><a href=\"admin.php?page=rsvp_add_attendees&p=delete&id=$listres->id\" title=\"delete $listres->title $listres->lastname\" onclick=\"if (confirm('Are You Sure you want to delete $listres->title $listres->lastname?')) { new Ajax.Updater('guestlist','rsvp-admin.php?p=delete&m=ajax&id=$listres->id'); return false;} else { return false; }\"><img src=\"/wp-includes/images/redx.gif\" style=\"border:none;height:10px;\" /> delete</a></td>";
			} else {
				$output .= "<td></td>";
			}
		$output .= "</tr>";
	}
	$output.= "</tbody></table>";
	
	return($output);
}


function rsvp_list_attendees(){

?>
	<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">Attendee List</h3>
					<div style="margin-bottom:7px;">
						<input type="button" class="button" value="refresh" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						<img src="/wp-content/themes/natural-essence-10/img/activity.gif" alt="Activity!" title="please wait..." style="height:14px;display:none;" id="refreshactivityind" />
					</div>
					<div style="margin-bottom:7px;">
						Sort:
						<input type="button" class="button" value="alpha" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						<input type="button" class="button" value="reverse" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&sort=alpharev',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						&nbsp;|&nbsp;
						<input type="button" class="button" value="newest" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&sort=newest',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						<input type="button" class="button" value="last updated" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&sort=lastupdated',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						&nbsp;|&nbsp;
						<input type="button" class="button" value="attending" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&sort=attending',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
						<input type="button" class="button" value="not attending" onclick="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&sort=notattending',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" /><br />
					</div>
					<div style="margin-bottom:7px;">
						Filter:&nbsp;&nbsp;<input type="text" id="filter" name="filter" onKeyUp="new Ajax.Updater('guestlist','/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList&s='+this.value,{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});" /> <input type="button" class="button" value="reset" onclick="$('filter').value='';new Ajax.Updater('guestlist',/wp-content/plugins/rsvp/rsvp-ajax.php?p=ajaxGetGuestList',{onCreate:function(){$('refreshactivityind').show();}, onComplete:function(){$('refreshactivityind').hide();}});return false;" />
					</div>
					<div style="font-size:86%;margin:0px;" id="guestlist">
						<?=print_guestlist();?>
					</div>
<?
}

function print_guestform($id=0) {
	global $wpdb;
	global $table_name;
		
	if ( $id == "" || $id == null ) {
		$id=0;
	}
	else if ( $id == 0 ){
		//nothing
	} else {
		//we got a number...
		$sql = "SELECT * FROM $table_name WHERE id=$id";
		$editRes = $wpdb->get_results($sql);

		foreach ($editRes as $e) {
			$id = $e->id;
			$code = $e->code;
			$title = $e->title;
			$first = $e->firstname;
			$last = $e->lastname;
			$namesuffix = $e->namesuffix;
			$family = $e->family;
			$address = $e->address;
			$city = $e->city;
			$state = $e->state;
			$zip = $e->zip;
			$phone = $e->phone;
			$partycount = $e->partycount;
			$priority = $e->priority;
		}
	}
	
	if ( $id != 0 ) { ?>
		<form id="guestform-form" action="admin.php?page=rsvp_add_attendees" method="POST" onsubmit="if ( myValidate() ) { new Ajax.Updater('guestlist','rsvp-admin.php?p=submit-edit&amp;m=ajax&amp;sort=lastupdated', {parameters:Form.serialize('guestform-form'), method: 'post', asynchronous: true, onCreate:function(){$('formactivityind').show();}, onComplete:function() { new Ajax.Updater('guestform','rsvp-admin.php?p=ajaxGetGuestForm',{evalScripts:true}); $('formactivityind').hide(); } } ); return false; } else { return false; }">
			<input type="hidden" name="p" value="submit-edit" />
			<input type="hidden" name="id" value="<?=$id;?>" />
	<?php } else { ?>
		<form id="guestform-form" action="admin.php?page=rsvp_add_attendees" method="POST" onsubmit="if ( myValidate() ) {new Ajax.Updater('guestlist' ,'rsvp-admin.php?p=submit&amp;m=ajax',     {parameters:Form.serialize('guestform-form'), method: 'post', asynchronous: true, onCreate:function(){$('formactivityind').show();}, onComplete:function() { $('guestform-form').reset(); valid.reset(); $('formactivityind').hide(); }}); return false; } else { return false; }">
			<input type="hidden" name="p" value="submit" />
	<?php } ?>
			<div style="margin-bottom:7px;">
				Title <select name="title" id="title" />
						<option <?php if ($title=="Mr.") echo "selected=\"SELECTED\""; ?>>Mr.</option>
						<option <?php if ($title=="Mrs.") echo "selected=\"SELECTED\""; ?>>Mrs.</option>
						<option <?php if ($title=="Mr. & Mrs.") echo "selected=\"SELECTED\""; ?>>Mr. &amp; Mrs.</option>
						<option <?php if ($title=="Miss") echo "selected=\"SELECTED\""; ?>>Miss</option>
						<option <?php if ($title=="Ms.") echo "selected=\"SELECTED\""; ?>>Ms.</option>
						<option <?php if ($title=="Dr. & Mrs.") echo "selected=\"SELECTED\""; ?>>Dr. &amp; Mrs.</option>
						<option <?php if ($title=="Mr. & Dr.") echo "selected=\"SELECTED\""; ?>>Mr. &amp; Dr.</option>
						<option <?php if ($title=="Pastor & Mrs.") echo "selected=\"SELECTED\""; ?>>Pastor. &amp; Mrs.</option>						
					</select>
			</div>
			<div style="margin-bottom:7px;">
				<div style="display:inline;width:250px;">First <input type="text" id="first" name="first" size="10" class="required" value="<?=$first;?>"/></div>
				Last <input type="text" id="last" name="last" size="10" class="required" value="<?=$last;?>" /> <select name="namesuffix" id="namesuffix" />
						<option <?php if ($namesuffix=="") echo "selected=\"SELECTED\""; ?>></option>
						<option <?php if ($namesuffix=="Sr.") echo "selected=\"SELECTED\""; ?>>Sr.</option>
						<option <?php if ($namesuffix=="Jr.") echo "selected=\"SELECTED\""; ?>>Jr.</option>
					</select>
			</div>
			<div style="margin-bottom:7px;">
				<p>
					Address <input type="text" id="address" name="address" size="25" value="<?=$address;?>" />
				</p>
				<p>
					City <input type="text" id="city" name="city" id="city" size="10" value="<?=$city;?>" />
					State <select id="state" name="state" />
						<option value="AL" <?php if ($state=="AL") echo "selected=\"SELECTED\""; ?>>AL</option>
						<option value="AK" <?php if ($state=="AK") echo "selected=\"SELECTED\""; ?>>AK</option>
						<option value="AZ" <?php if ($state=="AZ") echo "selected=\"SELECTED\""; ?>>AZ</option>
						<option value="AR" <?php if ($state=="AR") echo "selected=\"SELECTED\""; ?>>AR</option>
						<option value="CA" <?php if ($state=="CA") echo "selected=\"SELECTED\""; ?>>CA</option>
						<option value="CO" <?php if ($state=="CO") echo "selected=\"SELECTED\""; ?>>CO</option>
						<option value="CT" <?php if ($state=="CT") echo "selected=\"SELECTED\""; ?>>CT</option>
						<option value="DE" <?php if ($state=="DE") echo "selected=\"SELECTED\""; ?>>DE</option>
						<option value="DC" <?php if ($state=="DC") echo "selected=\"SELECTED\""; ?>>DC</option>
						<option value="FL" <?php if ($state=="FL") echo "selected=\"SELECTED\""; ?>>FL</option>
						<option value="GA" <?php if ($state=="GA") echo "selected=\"SELECTED\""; ?>>GA</option>
						<option value="HI" <?php if ($state=="HI") echo "selected=\"SELECTED\""; ?>>HI</option>
						<option value="ID" <?php if ($state=="ID") echo "selected=\"SELECTED\""; ?>>ID</option>
						<option value="IL" <?php if ($state=="IL") echo "selected=\"SELECTED\""; ?>>IL</option>
						<option value="IN" <?php if ($state=="IN") echo "selected=\"SELECTED\""; ?>>IN</option>
						<option value="IA" <?php if ($state=="IA") echo "selected=\"SELECTED\""; ?>>IA</option>
						<option value="KS" <?php if ($state=="KS") echo "selected=\"SELECTED\""; ?>>KS</option>
						<option value="KY" <?php if ($state=="KY") echo "selected=\"SELECTED\""; ?>>KY</option>
						<option value="LA" <?php if ($state=="LA") echo "selected=\"SELECTED\""; ?>>LA</option>
						<option value="ME" <?php if ($state=="ME") echo "selected=\"SELECTED\""; ?>>ME</option>
						<option value="MD" <?php if ($state=="MD") echo "selected=\"SELECTED\""; ?>>MD</option>
						<option value="MA" <?php if ($state=="MA") echo "selected=\"SELECTED\""; ?>>MA</option>
						<option value="MI" <?php if ($state=="MI") echo "selected=\"SELECTED\""; ?>>MI</option>
						<option value="MN" <?php if ($state=="MN") echo "selected=\"SELECTED\""; ?>>MN</option>
						<option value="MS" <?php if ($state=="MS") echo "selected=\"SELECTED\""; ?>>MS</option>
						<option value="MO" <?php if ($state=="MO") echo "selected=\"SELECTED\""; ?>>MO</option>
						<option value="MT" <?php if ($state=="MT") echo "selected=\"SELECTED\""; ?>>MT</option>
						<option value="NE" <?php if ($state=="NE") echo "selected=\"SELECTED\""; ?>>NE</option>
						<option value="NV" <?php if ($state=="NV") echo "selected=\"SELECTED\""; ?>>NV</option>
						<option value="NH" <?php if ($state=="NH") echo "selected=\"SELECTED\""; ?>>NH</option>
						<option value="NJ" <?php if ($state=="NJ") echo "selected=\"SELECTED\""; ?>>NJ</option>
						<option value="NM" <?php if ($state=="NM") echo "selected=\"SELECTED\""; ?>>NM</option>
						<option value="NY" <?php if ($state=="NY") echo "selected=\"SELECTED\""; ?>>NY</option>
						<option value="NC" <?php if ($state=="NC") echo "selected=\"SELECTED\""; ?>>NC</option>
						<option value="ND" <?php if ($state=="ND") echo "selected=\"SELECTED\""; ?>>ND</option>
						<option value="OH" <?php if ($state=="OH") echo "selected=\"SELECTED\""; ?>>OH</option>
						<option value="OK" <?php if ($state=="OK") echo "selected=\"SELECTED\""; ?>>OK</option>
						<option value="OR" <?php if ($state=="OR") echo "selected=\"SELECTED\""; ?>>OR</option>
						<option value="PA" <?php if ($state=="PA") echo "selected=\"SELECTED\""; ?>>PA</option>
						<option value="RI" <?php if ($state=="RI") echo "selected=\"SELECTED\""; ?>>RI</option>
						<option value="SC" <?php if ($state=="SC") echo "selected=\"SELECTED\""; ?>>SC</option>
						<option value="SD" <?php if ($state=="SD") echo "selected=\"SELECTED\""; ?>>SD</option>
						<option value="TN" <?php if ($state=="TN") echo "selected=\"SELECTED\""; ?>>TN</option>
						<option value="TX" <?php if ($state=="TX") echo "selected=\"SELECTED\""; ?>>TX</option>
						<option value="UT" <?php if ($state=="UT") echo "selected=\"SELECTED\""; ?>>UT</option>
						<option value="VT" <?php if ($state=="VT") echo "selected=\"SELECTED\""; ?>>VT</option>
						<option value="VA" <?php if ($state=="VA") echo "selected=\"SELECTED\""; ?>>VA</option>
						<option value="WA" <?php if ($state=="WA") echo "selected=\"SELECTED\""; ?>>WA</option>
						<option value="WV" <?php if ($state=="WV") echo "selected=\"SELECTED\""; ?>>WV</option>
						<option value="WI" <?php if ($state=="WI") echo "selected=\"SELECTED\""; ?>>WI</option>
						<option value="WY" <?php if ($state=="WY") echo "selected=\"SELECTED\""; ?>>WY</option>				
					</select>
					<div class="autocomplete" style="display:none;" id="autocity"></div>
					Zip <input type="text" name="zip" id="zip" size="5" class="validate-zip" value="<?=$zip;?>"/>
					<div class="autocomplete" style="display:none;" id="autozip"></div>
				
				</p>
			</div>
			<div style="margin-bottom:7px;">Phone <input type="text" id="phone" name="phone" class="validate-phone" value="<?=$phone;?>"/></div>
			<div style="margin-bottom:7px;">Count <input type="text" id="partycount" name="partycount" size="3" class="required validate-number-no-0" value="<?=$partycount;?>" /></div>
			<div style="margin-bottom:7px;">Priority <select name="priority" id="priority" />
				<option value="0" <?php if ($priority=="0") echo "selected=\"SELECTED\""; ?>>Definitely</option>
				<option value="1" <?php if ($priority=="1") echo "selected=\"SELECTED\""; ?>>Maybe</option>
				<option value="2" <?php if ($priority=="2") echo "selected=\"SELECTED\""; ?>>Maybe Maybe</option>
			</select></div>
			<div style="margin-bottom:7px;">Code <input type="text" id="code" name="code" class="validate-wedding-code" value="<?=$code;?>" size="7"/></div>
			<?php if ( $id != 0 ) {?>
			<input type="submit" class="button" value="update" id="submitbtn" /> <input type="button" class="button" value="cancel" onclick="new Ajax.Updater('guestform','rsvp-admin.php?p=ajaxGetGuestForm');new Ajax.Updater('guestlist','rsvp-admin.php?p=ajaxGetGuestList',{onCreate:function(){$('formactivityind').show();}, onComplete:function(){$('formactivityind').hide();}});"> <input type="button" class="button" value="validate" onclick="var tempValid = new Validation('guestform-form'); tempValid.validate();return false;">  <img src="/wp-content/themes/natural-essence-10/img/activity.gif" alt="Activity!" title="please wait..." style="height:14px;display:none;" id="formactivityind" />
			<?php } else { ?>
			<input type="submit" class="button" value="go" id="submitbtn" /> <input type="button" class="button" value="reset" onclick="Form.reset('guestform-form'); valid.reset(); new Ajax.Updater('guestlist','rsvp-admin.php?p=ajaxGetGuestList'); return false;"> <input type="button" class="button" value="validate" onclick="var tempValid = new Validation('guestform-form'); tempValid.validate();return false;"> <img src="/wp-content/themes/natural-essence-10/img/activity.gif" alt="Activity!" title="please wait..." style="height:14px;display:none;" id="formactivityind" />
			<?php } ?>
		</form>
		<script type="text/javascript">
			var cities = [
				<?php 
				$citysql = "SELECT DISTINCT city FROM $table_name";
				$cityresult = $wpdb->get_results($citysql);
				foreach ($cityresult as $c) {
					echo "'$c->city',";
				}?>
			];
			new Autocompleter.Local('city', 'autocity', cities, { fullSearch:true,frequency:0.2 });
		
			var zips = [
				<?php 
				$zipsql = "SELECT DISTINCT zip FROM $table_name";
				$zipresult = $wpdb->get_results($zipsql);
				foreach ($zipresult as $z) {
					echo "'$z->zip',";
				}?>
			];
			new Autocompleter.Local('zip', 'autozip', zips, { fullSearch:true,frequency:0.2 });
			
			var valid = new Validation('guestform-form',{ immediate:true });
			
			function myValidate() {
				var tempValid = new Validation('guestform-form'); 
				var result = tempValid.validate();
				
				return result;
			}
			
		</script>
<?php

}

function rsvp_add_attendees() {

	if ($_REQUEST['p'] != 'edit'){

	?>
	<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">Add Attendees</h3>
	<?

	print_guestform();
	}
}

function grab_values() {
	global $errormsg, $title, $first, $last, $namesuffix, $address, $city, $state,$zip,$phone,$partycount,$priority,$code;
	$errormsg = false;
	if (cleaninput($_REQUEST['title']))
		$title = cleaninput($_REQUEST['title']);
	else
		$errormsg = "(Title Blank)";
		
	if (cleaninput($_REQUEST['first']))
		$first = cleaninput($_REQUEST['first']);
	else
		$errormsg = "(First Name Blank)";
		
	if (cleaninput($_REQUEST['last']))
		$last = cleaninput($_REQUEST['last']);
	else
		$errormsg = "(Last Name Blank)";
	
	$namesuffix = cleaninput($_REQUEST['namesuffix']);
	
	$address = cleaninput($_REQUEST['address']);
	$city = cleaninput($_REQUEST['city']);
	$state = cleaninput($_REQUEST['state']);
	$zip = cleaninput($_REQUEST['zip']);
	$phone = cleaninput($_REQUEST['phone']);
	$code = cleaninput($_REQUEST['code']);
	
	if (cleaninput($_REQUEST['partycount']))
		$partycount = cleaninput($_REQUEST['partycount']);
	else
		$errormsg = "(Party Count Blank)";
	
	$priority = cleaninput($_REQUEST['priority']);

	return $errormsg;
}

function build_vcard($id) {
	global $wpdb;
	global $table_name;

        if ( $id == "" || $id == null ) {
                $id=0;
        }
        else if ( $id == 0 ){
                //nothing
        } else {
                //we got a number...
                $sql = "SELECT * FROM $table_name WHERE id=$id";
                $editRes = $wpdb->get_results($sql);

                foreach ($editRes as $e) {
                        $id = $e->id;
                        $title = $e->title;
                        $first = $e->firstname;
                        $last = $e->lastname;
                        $namesuffix = $e->namesuffix;
                        $family = $e->family;
                        $address = $e->address;
                        $city = $e->city;
                        $state = $e->state;
                        $zip = $e->zip;
                        $phone = $e->phone;
                        $partycount = $e->partycount;
                        $priority = $e->priority;
			if ($e->dateupdated != '0000-00-00 00:00:00') {
				$vdate = mysql_to_vcard($e->dateupdated);
			} else if ($e->dateadded != '0000-00-00 00:00:00') {
				$vdate = mysql_to_vcard($e->dateadded);
			} else {
				$vdate = mysql_to_vcard('');
			}
                }
        }



	header("Content-type: text/directory");
	header("Content-Disposition: attachment; filename=$first$last.vcf");


echo "BEGIN:VCARD\r\n
VERSION:2.1
N:$last;$first
FN:$first $lastp
TEL;HOME;VOICE:$phone
ADR;HOME:;;$address;$city;$state;$zip;United States of America
LABEL;HOME;ENCODING=QUOTED-PRINTABLE:$address=0D=0A$city, $state $zip=0D=0AUnited States of America
REV:$vdate
END:VCARD";	

} 



function rsvp_options(){
	$menuitems = get_option('rsvp_menuitems');
?>
<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">RSVP Options</h3>
<div id="generaloptions">
			<h2>General Options</h2>
			<form name="rsvpoptions" method="post" action="options.php">

			<?php settings_fields( 'rsvp_options' ); ?>

				<table class="form-table">
					<tr valign="top">
						<th align="left">Ceremony</th>
						<td><input  type="checkbox" name="rsvp_ceremony" value="checked" <?php echo get_option('rsvp_ceremony'); ?> /></td>
						<td><span class="setting-description">Should we process attendees for the ceremony?</span></td>
					</tr>
					<tr valign="top">
						<th align="left">Ceremony Date</th>
						<td><input  type="text" size="15" name="rsvp_ceremonydate" value="<?php echo get_option('rsvp_ceremonydate'); ?>"/></td>
						<td>What is the date of the ceremony?</td>
					</tr>
					<tr valign="top">
						<th align="left">Reception</th>
						<td><input  type="checkbox" name="rsvp_reception" value="checked" <?php echo get_option('rsvp_reception'); ?> /></td>
						<td>Should we process attendees for the Reception?</td>
					</tr>
					<tr valign="top">
						<th align="left">Reception Date</th>
						<td><input  type="text" size="15" name="rsvp_receptiondate" value="<?php echo get_option('rsvp_receptiondate'); ?>"/></td>
						<td>What is the date of the reception?</td>
					</tr>
					<tr valign="top">
						<th align="left">Children</th>
						<td><input  type="checkbox" name="rsvp_children" value="checked" <?php echo get_option('rsvp_children'); ?> /></td>
						<td>Do we need to ask if children will be attending?</td>
					</tr>
					<tr valign="top">
						<th align="left">Thank You</th>
						<td><input type="text" size="20" name="rsvp_tymsg"><?php echo get_option('rsvp_tymsg');?></textarea></td>
						<td>This message will be displayed after the guests submit their RSVP</td>
					</tr>					
				</table>
			<h2>Reception Options</h2>
				<table class="form-table">
					<tr valign="top">
						<th align="left">Reception Menu Options</th>
						<td><select name="rsvp_menuitems">
							<option value="1" <?php echo( $menuitems == 1 ?' selected="selected"':null) ?>>1</option>
							<option value="2" <?php echo( $menuitems == 2 ?' selected="selected"':null) ?>>2</option>
							<option value="3" <?php echo( $menuitems == 3 ?' selected="selected"':null) ?>>3</option>
							<option value="3" <?php echo( $menuitems == 4 ?' selected="selected"':null) ?>>4</option>
							<option value="3" <?php echo( $menuitems == 5 ?' selected="selected"':null) ?>>5</option>
							</select>
						<span class="setting-description">How many menu item choices?</span></td>
					</tr>
<?php					
				for ($i = 1; $i <= $menuitems; $i++) {
?>					
					<tr valign="top">
						<th align="left">Menu Item Choice #<?php echo $i?></th>
						<td><textarea cols="40" rows="5" name=<?php echo '"rsvp_menuitem' . $i . '">' . get_option('rsvp_menuitem' . $i);?></textarea>
						Description of Menu Item Choice #<?php echo $i?></td>
					</tr>
<?php					
				}	
?>										
				</table>
			<p>	
			<input class="button" type="submit" name="updateoption" value="Save Changes"/>
			
			</form>	
		</div>	

<?

}


function rsvp_list_meals() {
	global $wpdb;
	global $table_name;
	global $meal_table;
	$menuitems = get_option( 'rsvp_menuitems' );
	
	$query = "SELECT t1.title, t1.lastname, t1.firstname, GROUP_CONCAT(t2.choice SEPARATOR ',') AS choice, GROUP_CONCAT(t2.qty SEPARATOR ',') AS qty FROM " . $table_name . " AS t1 INNER JOIN " . $meal_table . " AS t2 ON t1.id=t2.id GROUP BY t1.id";
	$data = $wpdb->get_results($query);


?>
	<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">RSVP Meal Selections</h3>
	<table id="rsvp-meal-table">
		<thead>
			<tr>
				<th>Name</th>
<?php
		for ($i = 1; $i <= $menuitems; $i++) {
?>
				<th> Meal Choice <?php echo $i;?></th>
<?php
		}
?>
			</tr>
		</thead>
		<tfoot>
			<tr>
<?php
	$query = "SELECT choice, SUM(qty) FROM " . $meal_table . " GROUP BY choice ORDER BY choice ASC";
	$totals = $wpdb->get_results($query, ARRAY_N);
		
	echo '<td class="right">Totals</td>';
    for ($i = 1; $i <= $menuitems; $i++) {
		echo '<td>';
		for ($j = 0; $j <= $menuitems-1; $j++) {		    	
			if ($totals[$j][0] == $i) {
		    	echo $totals[$j][1];
		   	}
		}
		echo '</td>';
		
	}
?>
			</tr>
		</tfoot>
		</tbody>
<?php
		foreach ( $data as $data )
		{

			$choice = explode(",",$data->choice);
			$qty = explode(",",$data->qty);

		    echo '<tr>';
		    echo '<td class="left">' . $data->title . ' ' . $data->firstname . ' ' . $data->lastname . '</td>';
		    		    
		    for ($i = 1; $i <= $menuitems; $i++) {
		    	echo '<td>';
		    	for ($j = 0; $j <= $menuitems-1; $j++) {		    	
		    		if ($choice[$j] == $i) {
		    			echo $qty[$j];
		    		} 
		    	}	
		    		
		    	echo '</td>';
			} 
		    echo '</tr>';
		}
		

?>
			
		</tbody>
	</table>
		
<?php
}

function rsvp_import_export() {

?>
			<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">Import/Export</h3>

			Export the Guest List to a CSV file.  You may then use the CSV for mail merge, etc.			
			<button onClick="window.location='/wp-content/plugins/rsvp/rsvp-ajax.php?p=getExport'">Export CSV</button>
<?php


}


// Export code originally from http://www.ineedtutorials.com/code/php/export-mysql-data-to-csv-php-tutorial
function exportMysqlToCsv($table,$filename = 'export.csv')
{
    $csv_terminated = "\n";
    $csv_separator = ",";
    $csv_enclosed = '"';
    $csv_escaped = "\\";
    $sql_query = "select * from $table";
 
    // Gets the data from the database
    $result = mysql_query($sql_query);
    $fields_cnt = mysql_num_fields($result);
 
 
    $schema_insert = '';
 
    for ($i = 0; $i < $fields_cnt; $i++)
    {
        $l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,
            stripslashes(mysql_field_name($result, $i))) . $csv_enclosed;
        $schema_insert .= $l;
        $schema_insert .= $csv_separator;
    } // end for
 
    $out = trim(substr($schema_insert, 0, -1));
    $out .= $csv_terminated;
 
    // Format the data
    while ($row = mysql_fetch_array($result))
    {
        $schema_insert = '';
        for ($j = 0; $j < $fields_cnt; $j++)
        {
            if ($row[$j] == '0' || $row[$j] != '')
            {
 
                if ($csv_enclosed == '')
                {
                    $schema_insert .= $row[$j];
                } else
                {
                    $schema_insert .= $csv_enclosed . 
					str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $row[$j]) . $csv_enclosed;
                }
            } else
            {
                $schema_insert .= '';
            }
 
            if ($j < $fields_cnt - 1)
            {
                $schema_insert .= $csv_separator;
            }
        } // end for
 
        $out .= $schema_insert;
        $out .= $csv_terminated;
    } // end while
 
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Length: " . strlen($out));
    // Output to browser with appropriate mime type, you choose ;)
    header("Content-type: text/x-csv");
    //header("Content-type: text/csv");
    //header("Content-type: application/csv");
    header("Content-Disposition: attachment; filename=$filename");
    echo $out;
    exit;
 
}



$p = $_REQUEST['p'];
$m = $_REQUEST['m'];
$id = cleaninput($_REQUEST['id']);


if ($p == "submit" && $m == "ajax") {
	
	if (grab_values()) {
		echo "<div style='border:2px solid red; font-weight:bold;'>Error: $errormsg</div>";
		echo print_guestlist();
		exit;
	}
	// echo $title.$first.$last;
	$sql = "INSERT INTO guestlist (`lastname`,`firstname`,`title`,`namesuffix`,`address`,`city`,`state`,`zip`,`partycount`,`phone`,`priority`,`useradded`,`dateadded`, `code`) VALUES ('$last','$first','$title','$namesuffix','$address','$city','$state','$zip','$partycount','$phone','$priority','$userdata->user_login',NOW(), '$code')";
	// echo $sql;
	
	dbQuery($sql);
	echo print_guestlist();
	exit;
	
} else if ( $p == "submit" ) {
	
	grab_values();
	$sql = "INSERT INTO $table_name (`lastname`,`firstname`,`title`,`namesuffix`,`address`,`city`,`state`,`zip`,`partycount`,`phone`,`priority`,`useradded`,`dateadded`, `code`) VALUES ('$last','$first','$title','$namesuffix','$address','$city','$state','$zip','$partycount','$phone','$priority','$userdata->user_login',NOW(), '$code')";
	$wpdb->query($sql);
	exit;

} else if ($p == "submit-edit" && $m == "ajax" ) {
	grab_values();
	$sql = "UPDATE $table_name SET title='$title', firstname='$first', lastname='$last',namesuffix='$namesuffix',address='$address',city='$city',state='$state',zip='$zip',partycount='$partycount',phone='$phone', priority='$priority', userupdated='$userdata->user_login', code='$code' WHERE id=$id LIMIT 1";
	// echo $sql;
	$wpdb->query($sql);
	echo print_guestlist();
	exit;

} else if ($p == "submit-edit") {
	
	grab_values();
	$sql = "UPDATE $table_name SET title='$title', firstname='$first', lastname='$last',namesuffix='$namesuffix',address='$address',city='$city',state='$state',zip='$zip',partycount='$partycount',phone='$phone', priority='$priority', userupdated='$userdata->user_login', code='$code' WHERE id=$id LIMIT 1";
	$wpdb->query($sql);
	exit;
	
} else if ( $p == "edit" ) {

	?>
	<h3 style="padding-bottom:3px; margin-bottom:15px;border-bottom:2px solid #553">Edit Attendees</h3>
	<?

	if ( $id != "" && $id != null) {
		print_guestform($id);
	} else {
		print_guestform();
	}
	
} else if ( $p == "delete" && $id != false) { 	
	
	$sql = "DELETE FROM $table_name WHERE id = '$id' LIMIT 1";	
	$wpdb->query($sql);
		
} else if ( $p == "ajaxGetGuestList") {
	
	echo print_guestlist();
	exit;

} else if ( $p == "ajaxGetGuestForm") {
	
	echo print_guestform($id);
	exit;
	
} else if ( $p == "getvcard" ) {
	
	build_vcard($id);
	exit;
	
} else if ( $p == "getExport" ) {

	exportMysqlToCsv($table_name, "guestlist.csv");

}

?>
