<?php
/**
 * $Header$
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Look up Topic (lookup_inc is universal, gContent == BitBoardTopic)
require_once( BOARDS_PKG_INCLUDE_PATH.'lookup_inc.php' );

// Make sure topic exists since we only run through here for existing topics. New topics are created via comment system.
if( !$gContent->isValid() ){
	$gBitSystem->fatalError( tra('No topic specified.') );
}

// Check the user's ticket
$gBitUser->verifyTicket();

// Load up the Topic's board - we'll respect its permissions
$board = new BitBoard( $gContent->mInfo['board_id'] );
$board->load();

$rslt = false;

// Edit calls
// Set locked or sticky
if( isset($_REQUEST['is_locked']) || isset($_REQUEST['is_sticky']) ){
	// Check permissions to edit this topic
	$board->verifyUpdatePermission();
	
	if ( isset($_REQUEST['is_locked']) && is_numeric($_REQUEST['is_locked']) ){
		$rslt = $gContent->lock($_REQUEST['is_locked']);
	} elseif ( isset($_REQUEST['is_sticky']) && is_numeric($_REQUEST['is_sticky']) ){
		$rslt = $gContent->sticky($_REQUEST['is_sticky']);
	}
// Remove a topic
}elseif( isset( $_REQUEST['remove'] ) ) {
	// Check permissions to edit this topic if the root object is the board check its perms, otherwise check general comment admin perms
	if( !(( $gContent->mInfo['root_id'] == $gContent->mInfo['board_content_id'] && $board->hasAdminPermission() ) || $gBitUser->hasPermission('p_liberty_admin_comments')) ){
		$gBitSystem->fatalError( tra('You do not have permission to delete this topic.') );
	}
	
	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['remove'] = TRUE;
		$formHash['t'] = $_REQUEST['t'];
		$gBitSystem->confirmDialog( $formHash, 
			array( 
				'warning' => tra( 'Are you sure you want to delete this topic?' ) . ' ' . $gContent->getTitle(),
				'error' => tra('This cannot be undone!')
			)
		);
	} else {
		// @TODO Topic should extend LibertyComment - but until that day we load it up a second time
		$topicAsComment = new LibertyComment( $_REQUEST['t'] );
		if( !$topicAsComment->expunge() ) {
			$gBitSmarty->assignByRef( 'errors', $topicAsComment->mErrors );
		}
		// send us back to the baord - http_referer won't work with confirm process 
		bit_redirect( BOARDS_PKG_URL.'index.php?b='. $gContent->mInfo['board_id'] );
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
