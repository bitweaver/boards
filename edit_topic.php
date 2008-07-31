<?php

require_once( '../bit_setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Look up Topic (lookup_inc is universal, gContent == BitBoardTopic)
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

// Make sure topic exists since we only run through here for existing topics. New topics are created via comment system.
if( !$gContent->isValid() ){
	$gBitSystem->fatalError( 'No topic specified' );
}

// Check the user's ticket
$gBitUser->verifyTicket();

$rslt = false;
// Edit calls
if( isset($_REQUEST['is_locked']) || isset($_REQUEST['is_sticky']) ){
	// Check permissions to edit this topic
	$gContent->verifyEditPermission();
	
	if ( isset($_REQUEST['is_locked']) && is_numeric($_REQUEST['is_locked']) ){
		$rslt = $gContent->lock($_REQUEST['is_locked']);
	} elseif ( isset($_REQUEST['is_sticky']) && is_numeric($_REQUEST['is_sticky']) ){
		$rslt = $gContent->sticky($_REQUEST['is_sticky']);
	}
// User pref options on a topic - not really editing but this simplifies topic related processes putting it here
}elseif( isset($_REQUEST['new']) || isset($_REQUEST['notify']) ){
	// Check permissions to view this topic
	$gContent->verifyViewPermission();

	if( isset($_REQUEST['new']) && is_numeric($_REQUEST['new']) ){
		$rslt = $gContent->readTopicSet($_REQUEST['new']);
	}elseif( isset($_REQUEST['notify']) && is_numeric($_REQUEST['notify']) ){
		$rslt = $gContent->notify($_REQUEST['notify']);
	}
}

if($rslt){
	// Return us to where we came from
	header ("location: ".$_SERVER['HTTP_REFERER']);
}else{
	// @TODO put error into an alert
	//trigger_error(var_export($gContent->mErrors,true ));
}

?>
