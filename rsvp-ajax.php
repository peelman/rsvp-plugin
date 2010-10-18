<?php
require_once( dirname (__FILE__) . '/rsvp-common.php');
require_once( dirname (__FILE__) . '/rsvp-config.php');

if (! current_user_can('upload_files') ) {
	//header('Location:http://www.wedding.aiman.net/wp-login.php?redirect_to=rsvp-admin.php');
	exit;
}

require_once( dirname (__FILE__) . '/rsvp-admin.php');

?>