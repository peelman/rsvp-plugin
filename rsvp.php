<?php
/*
Plugin Name: Wedding RSVP
Plugin URI: http://www.managedinsanity.com/rsvp-plugin
Description: A brief description of the Plugin.
Version: 1.0
Author: Mark Aiman & Nick Peelman
Author URI: http://www.managedinsanity.com
*/

/*  Copyright 2009  Mark Aiman  (email : maaiman@aiman.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function rsvp_install () {
   global $wpdb;
   $rsvp_db_version = "1.0";

   $table_name = $wpdb->prefix . "rsvp_guestlist";
   $menu_table = $wpdb->prefix . "rsvp_menu";

   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
      id int(11) NOT NULL auto_increment,
      code varchar(5) NOT NULL,
      title varchar(15) default NULL,
      lastname varchar(50) NOT NULL,
      firstname varchar(50) NOT NULL,
      namesuffix varchar(5) default NULL,
      address varchar(50) NOT NULL,
      city varchar(50) NOT NULL,
      state varchar(2) NOT NULL,
      zip varchar(5) NOT NULL,
      phone varchar(15) default NULL,
      partycount int(11) NOT NULL,
      partycountattending varchar(15) default NULL,
      partycountchildren int(11) NOT NULL,
      attendingwedding tinyint(4) NOT NULL,
      attendingreception tinyint(4) NOT NULL,
      priority int(11) NOT NULL default '0',
      useradded varchar(40) NOT NULL,
      userupdated varchar(40) default NULL,
      dateadded datetime NOT NULL,
      dateupdated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
      PRIMARY KEY  id (id)
	);";

	
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

	$sql = "CREATE TABLE " . $menu_table . " (
		`id` int(11) default NULL,
		`choice` tinyint(4) default NULL,
		`qty` tinyint(4) default NULL,
		PRIMARY KEY  (id, choice)
		);"; 

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

      add_option( "rsvp_db_version", $rsvp_db_version );
      add_option( "rsvp_db_tablename", $table_name );
      add_option( "rsvp_db_menu", $table_name2 );
      

   }
}

// Install Plugin 
register_activation_hook(__FILE__,'rsvp_install');

// Add the Admin Menu 
add_action('admin_menu', 'rsvp_admin_menu');

// Setup the Admin Menu
function rsvp_admin_menu() {
	add_menu_page('RSVP Admin', 'RSVP', 8, 'rsvp_options', 'show_admin_menu');
	add_submenu_page('rsvp_options', 'Options', 'Options', 8, 'rsvp_options', 'show_admin_menu');
	add_submenu_page('rsvp_options', 'Add Attendees', 'Add Attendees', 8, 'rsvp_add_attendees', 'show_admin_menu');
	add_submenu_page('rsvp_options', 'List Attendees', 'List Attendees', 8, 'rsvp_list_attendees', 'show_admin_menu');
	add_submenu_page('rsvp_options', 'List Meal Selections', 'List Meal Selections', 8, 'rsvp_list_meals', 'show_admin_menu');
	add_submenu_page('rsvp_options', 'Import/Export', 'Import/Export', 8, 'rsvp_import_export', 'show_admin_menu');
	add_action( 'admin_init', 'register_rsvp_options' ); 	
}

//add_action('admin_print_scripts', 'rsvp_admin_scripts');
add_action('init', 'rsvp_admin_scripts');

//add acouple of scripts to the admin site so ajax works
function rsvp_admin_scripts(){
	wp_enqueue_script('prototype');
	wp_enqueue_script('jquery');
}

function show_admin_menu() {

    
    		switch ($_GET['page']){
    		case "rsvp_options" :
				include_once ( dirname (__FILE__) . '/rsvp-admin.php' );		// 
				rsvp_options();
				break;
			case "rsvp_add_attendees" :
				include_once ( dirname (__FILE__) . '/rsvp-admin.php' );		// 
				rsvp_add_attendees();
				break;
			case "rsvp_list_attendees" :
				include_once ( dirname (__FILE__) . '/rsvp-admin.php' );		// 
				rsvp_list_attendees();
				break;
			case "rsvp_list_meals" :
				include_once ( dirname (__FILE__) . '/rsvp-admin.php' );		// 
				rsvp_list_meals();
				break;
			case "rsvp_import_export" :
				include_once ( dirname (__FILE__) . '/rsvp-admin.php' );		// 
				rsvp_import_export();
				break;
			}		
			
}

//Allow wordpress to handle some of the option setting in the administration options
function register_rsvp_options(){
	register_setting( 'rsvp_options', 'rsvp_ceremony' );
	register_setting( 'rsvp_options', 'rsvp_ceremonydate' );
	register_setting( 'rsvp_options', 'rsvp_reception' );
	register_setting( 'rsvp_options', 'rsvp_receptiondate' );
	register_setting( 'rsvp_options', 'rsvp_children' );
	register_setting( 'rsvp_options', 'rsvp_menuitems' );
	register_setting( 'rsvp_options', 'rsvp_menuitem1' );
	register_setting( 'rsvp_options', 'rsvp_menuitem2' );
	register_setting( 'rsvp_options', 'rsvp_menuitem3' );
	register_setting( 'rsvp_options', 'rsvp_menuitem4' );
	register_setting( 'rsvp_options', 'rsvp_menuitem5' );
	register_setting( 'rsvp_options', 'rsvp_tymsg' );
	

}

//Allow for some CSS styling on the Administration pages
function addrsvpCSS(){
	$url = get_settings('siteurl');
    $url = $url . '/wp-content/plugins/rsvp/rsvp.css';
    echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />';

}
add_action( 'admin_head', 'addrsvpCSS' );
add_action( 'wp_head', 'addrsvpCSS' );


// Function to start the RSVP Code entry.
function rsvp_first_page($code)
{ ?>
	Enter the code from your invitation and your last name into the form below and follow the instructions to digitally RSVP!  If there are any problems, please contact <a href="">us</a> (markandemily [at] wedding.aiman.net)!
	<div id="rsvp-code-form" style="margin-top:10px;">
		<form action="." >
			<fieldset>
			<label for="code">Enter your code word:</label><input id="code" name="code" type="text" size="10" value="<?=$code;?>"/> <br/>
			<label for="lastname">Enter your last name:</label><input id="lastname" name="lastname" type="text" size="20" value=""/>
			<label for="submit"></label><input type="submit" value="Continue" id="initial-submit-btn"/>
			<input type="hidden" name="p" value="processCode">
			</fieldset>
		</form>
	</div>
<?php }

// Add shortcode to start the user side RSVP process
add_shortcode('RSVP', 'rsvp_form_handler');



// Function to process RSVP page data submission and handle the Shortcode
function rsvp_form_handler() {
	require_once(dirname (__FILE__) . '/rsvp-common.php');
	global $wpdb;
	$table_name = get_option( 'rsvp_db_tablename' );
	$menu_table = get_option( 'rsvp_db_menu' );
	$menuitems = get_option( 'rsvp_menuitems' );
	$p = $_REQUEST['p'];
	$code = cleaninput($_REQUEST['code']);
	$id = cleaninput($_REQUEST['id']);
	$lastname = cleaninput($_REQUEST['lastname']);

if ( $p == 'processCode' )
{
	echo rsvp_getGuest(code_to_id($code, $lastname));
} 
else if ( $p == 'processAttendance' )
{
	$wedding = ($_REQUEST['wedding-checkbox'] == "on") ? 1 : 0;
	$reception = ($_REQUEST['reception-checkbox'] == "on") ? 1 : 0;
	$query = "UPDATE $table_name SET attendingwedding = $wedding, attendingreception = $reception WHERE id='$id'";
	$wpdb->query($query);
	attendance_details($id, $wedding, $reception);
}
else if ( $p == 'processAttendanceDetails' )
{
	$numattending = cleaninput($_REQUEST['adultcount']);
	$numattending += cleaninput($_REQUEST['childcount']);

	$query = "UPDATE $table_name SET partycountattending = '$numattending'  WHERE id='$id'";
	//echo $query;
	$wpdb->query($query);

	for ($i = 1; $i <= $menuitems; $i++) {
		$meal = cleaninput($_REQUEST['meal' . $i]);		
		if ( $meal != '0' ) {
			$query = "INSERT INTO $menu_table (`id`, `choice`, `qty`) VALUES ('$id', '$i', '$meal')";
			$wpdb->query($query);
		}
	}
	
	echo '<h2>Thank you!</h2>';
	echo get_option('rsvp_tymsg');
}
else if ( $p == 'page_one' )
{
	echo rsvp_first_page($code);
} 
else

	rsvp_first_page($code);
		
}

// Function to convert the RSVP code to the primary key ID
function code_to_id($code, $lastname)
{
	global $wpdb;
	$table_name = get_option( 'rsvp_db_tablename' );
	
	$query = "SELECT id FROM " . $table_name . " WHERE code='$code' and LOWER(lastname)='" . strtolower($lastname). "'";
	$result = $wpdb->get_var($query);
	
	
	if ( $result == 0 )	{
		return false;
	} else {
		return $result;
	}
	
}


// Function to get the Guest from the database and display
function rsvp_getGuest($id)
{ 
	global $wpdb;
	$table_name = get_option( 'rsvp_db_tablename' );
	if (!$id)
	{	?>
			<h4>Error: Invalid Code Entered</h4>
			You Must enter a valid code for this to work!<br />
			<input type="button" value="Back" onclick="history.go(-1);return false;" />

		<?php exit;	
	}
	$query = "SELECT * FROM $table_name WHERE id='$id' LIMIT 1";
	$g = $wpdb->get_row($query);

	 
		
		//TODO ADD CODE TO CHECK BOXES IF THEY HAVE ALREADY VISITED!
		
		?>
		
		<h2 style="margin-bottom:5px;">Welcome <?=$g->title . " " . $g->lastname?>!</h2>
		<form id="page_two" action=".">
			<input type="hidden" name="p" value="processAttendance">
			<input type="hidden" name="id" value=<?=$id?>>

			Will you be joining us?<br />
			<div style="margin-left:25px;margin-top:10px;">
				<input type="checkbox" id="wedding-checkbox" name="wedding-checkbox" <?php if ($g->attendingwedding == 1) { echo "checked=\"checked\""; } ?>/>&nbsp;&nbsp;<label style="display:inline;" for="wedding-checkbox">Yes, we will be attending the ceremony on <?php echo get_option( 'rsvp_ceremonydate' ); ?></label><br />
				<input type="checkbox" id="reception-checkbox" name="reception-checkbox" <?php if ($g->attendingreception == 1) { echo "checked=\"checked\""; } ?>/>&nbsp;&nbsp;<label style="display:inline;" for="reception-checkbox">Yes, we will be attending the reception on <?php echo get_option( 'rsvp_receptiondate' ); ?></label><br /><br />
				<input type="button" value="Back" onclick="history.go(-1);return false;"/> 
				<input type="submit" id="submit-btn" value="Next" />
			</div>
		</form>
		<br />

	<?php 
}  // End Function

function attendance_details($id, $wedding, $reception) {

	if ($wedding == 0 && $reception == 0)
	{
		attending_neither_form($id);
	}
	else 
	{
		attendance_details_form_header();
		
		if ( $wedding == 1 )
		{
			attending_wedding_form($id);
		} 
		if ( $reception == 1 )
		{
			attending_reception_form($id);
		}
		
		attendance_details_form_footer();		
	}
}

function attendance_details_form_header() {
	?>
	<form id="page_two" action=".">
				<input type="hidden" name="p" value="processAttendanceDetails">


<?php 
}

function attendance_details_form_footer() { ?>
		<input type="button" value="Back" onclick="history.go(-1);return false;" />
		<input type="submit" value="Continue" id="submit-btn"/>
	</form>
<?php
}

function attending_wedding_form($id)
{ 
	global $wpdb;
	$table_name = get_option( 'rsvp_db_tablename' );

	$query = "SELECT id, partycount, partycountchildren FROM $table_name WHERE id='$id' LIMIT 1";
	$g = $wpdb->get_row($query);

?>
	<h2>Ceremony</h2>
		<input type="hidden" name="ceremony" value="true" />
		<input type="hidden" name="id" value=<?=$id?>>

		<table>
		<?php if (get_option( 'rsvp_children' ) == 'checked' ) { ?>
		<tr>	<td>How many adults will be in your party?</td> <td><input type="text" value="<?=$g->partycount;?>" size="4" name="adultcount" id="adultcount" class="validate-number" onKeyUp="$('totalcount').value = ($('adultcount').value * 1) + ($('childcount').value * 1); return false;"/></td></tr>
		<tr>	<td>How many children will be in your party?</td> <td><input type="text" value="<?=$g->partycountchildren;?>" size="4" name="childcount" id="childcount" class="validate-number" onKeyUp="$('totalcount').value = ($('adultcount').value * 1) + ($('childcount').value * 1);" /></td></tr>
		<?php } else { ?>
			<tr><td>How many adults will be in your party?</td> <td><input type="text" value="<?=$g->partycount;?>" size="4" name="adultcount" id="adultcount" class="validate-number" onKeyUp="$('totalcount').value = ($('adultcount').value * 1); return false;"/></td></tr>
		<?php } ?>
		<tr><td>Total Number of Guests Attending:</td> <td><input type="text" value="<?=$g->partycount;?>" size="4" name="totalcount" id="totalcount" disabled="disabled" style="font-weight:bold;" /></td></tr>
		</table>

<?php 

}

function attending_reception_form($id)
{ 
	global $wpdb;
	$table_name = get_option( 'rsvp_db_tablename' );
	$menu_table = get_option( 'rsvp_db_menu' );
	$menuitems = get_option( 'rsvp_menuitems' );
	$menuitem = array ( 1 => get_option( 'rsvp_menuitem1' ) , 2 => get_option( 'rsvp_menuitem2' ) , 3 => get_option( 'rsvp_menuitem3' ) , 4 => get_option( 'rsvp_menuitem4' ) , 5 => get_option( 'rsvp_menuitem5' ) );
	
	$query = "SELECT id, partycount, partycountchildren FROM $table_name WHERE id='$id' LIMIT 1";
	$g = $wpdb->get_row($query);


?>
	<h2>Reception</h2>
		<input type="hidden" name="reception" value="true" />
		Please select your entree(s) for the reception:
		<br />
		<table class="form-table">
			
<?php	
	for ($i = 1; $i <= $menuitems; $i++) {
    	
    	$qty = $wpdb->get_var("SELECT qty FROM $menu_table WHERE id=$id and choice=$i"); 
    	
    	echo '<tr>';
    	echo '<td><input type="text" value="' . $qty . '" size="4" name="meal' .$i . '" /></td>';
    	echo '<td>' . $menuitem[$i] . '</td>';
    	echo '</tr>';
    	
	}	
?>		
		</table>
	<br />

<?php 
}

function attending_neither_form($id)
{ 
	
?>
	<h3>:(</h3>
		We are sorry to hear that you won't be attending, but we understand.  If your plans change, feel free to come back and re-enter your code!
		<br />

<?php 
}


?>