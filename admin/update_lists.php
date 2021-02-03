<?php
/**
 * utlity file to update settings for all mailing lists
 * its common to start up some mailing lists and then realize you need to 
 * customie the file util/mailman.cfg to get things tuned to ones liking
 * calling this file will update all your lists configuring them using
 * the settings in util/mailman.cfg
 *
 * WARNING!: If you are using boards with groups pkg and have different 
 * list moderation settings for each list this will set all lists to the
 * default moderation setting in mailman.cfg
 */

require_once( '../../kernel/includes/setup_inc.php' );
require_once( UTIL_PKG_INCLUDE_PATH.'mailman_lib.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

if( !$gBitUser->isAdmin() ){
	$gBitSystem->fatalError('You do not have permission to tinker with mailing lists, so scram');
}

if( !empty( $_REQUEST['update'] ) ) {
	// get all mailman lists
	$query = "SELECT pref_value AS listname FROM `".BIT_DB_PREFIX."liberty_content_prefs` lcp WHERE lcp.`pref_name` = ?";
	$lists =  $gBitSystem->mDb->getArray( $query, array('boards_mailing_list') ); 

	foreach( $lists as $list ){
		// update list configuration
		if( $error = mailman_config_list( array( 'listname' => $list['listname'] ) ) ) {
			echo $error."<br />";
		}else{
			echo "List: ".$list['listname']." has been updated.<br />";
		}
	}
}else{
	echo "Include 'update=y' in the url to trigger the update process";
}
