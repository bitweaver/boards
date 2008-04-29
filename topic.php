<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/Attic/topic.php,v 1.34 2008/04/29 02:12:49 wjames5 Exp $
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// This appears to be related to setting topics as having been read - but has a bug
if (isset($_REQUEST["new"])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_boards_read' );

	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
	$res = true;
	if (isset($_REQUEST["new"]) && is_numeric($_REQUEST["new"])) {
		// @TODO Debug: gContent appears to be the wrong content type, a BitBoard instead of a BitBoardTopic which has readTopicSet method
		$res = $gContent->readTopicSet($_REQUEST["new"]);
	}
	if ($res) {
		header ("location: ".$_SERVER['HTTP_REFERER']);
	} else {
		trigger_error(var_export($gContent->mErrors,true ));
	}
	die();
// This appears to be related to setting topics as being sticky or locked - but has a bug
} elseif (isset($_REQUEST["locked"]) || isset($_REQUEST["sticky"])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_boards_edit' );

	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
	// @TODO Debug: gContent appears to be the wrong content type, a BitBoard instead of a BitBoardTopic which has lock and sticky methods
	$res = true;
	if (isset($_REQUEST["locked"]) && is_numeric($_REQUEST["locked"])) {
		$res = $gContent->lock($_REQUEST["locked"]);
	} elseif (isset($_REQUEST["sticky"]) && is_numeric($_REQUEST["sticky"])) {
		$res = $gContent->sticky($_REQUEST["sticky"]);
	}
	if ($res) {
		header ("location: ".$_SERVER['HTTP_REFERER']);
	} else {
		trigger_error(var_export($gContent->mErrors,true ));
	}
	die();
// approve or reject ananymous comments
} elseif (!empty($_REQUEST['action'])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_boards_edit' );

	$comment = new BitBoardPost($_REQUEST['comment_id']);
	$comment->loadComment();
	if (!$comment->isValid()) {
		$gBitSystem->fatalError("Invalid Comment Id");
	}
	switch ($_REQUEST['action']) {
		case 1:
			// Aprove
			$comment->modApprove();
			break;
		case 2:
			// Reject
			$comment->modReject();
			break;
		default:
			break;
	}
} elseif ( @BitBase::verifyId( $_REQUEST['migrate_board_id'] ) ) {
	if( $_REQUEST['b'] = BitBoard::lookupByMigrateBoard( $_REQUEST['migrate_board_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?b='. $_REQUEST['b'] );
	}
}



// Finally we can get down to businesses - load up the board
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

if( !$gContent->isValid() ) {
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError( "The board you requested could not be found." );
}

$gContent->verifyViewPermission();

$displayHash = array( 'perm_name' => 'p_boards_read' );
$gContent->invokeServices( 'content_display_function', $displayHash );


/* A mass remove request might be made, handle it
 * Code is moved to edit_topic_inc to try to make this all a little more sane.
 */
require_once( BOARDS_PKG_PATH.'edit_topic_inc.php' );


// set some comment values since topics are comments
$commentsParentId=$gContent->mContentId;
$comments_return_url=  BOARDS_PKG_URL."index.php?b=".urlencode($gContent->mBitBoardId);

require_once( BOARDS_PKG_PATH.'boards_comments_inc.php' );

// get the topics for this board
$threads = new BitBoardTopic( $gContent->mContentId );
// lets pass in a ref to the root obj so we can fully mimic comments
$threads->mRootObj = $gContent; 
$threadsListHash = $_REQUEST;
$threadList = $threads->getList( $threadsListHash );

$gBitSmarty->assign_by_ref( 'threadList', $threadList );
// getList() has now placed all the pagination information in $_REQUEST['listInfo']
$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );

$gBitSmarty->assign_by_ref( 'board', $gContent );
$gBitSmarty->assign( 'cat_url', BOARDS_PKG_URL."index.php"); //?ct=".urlencode($gContent->mInfo['content_type_guid']));

$gBitThemes->loadAjax( 'mochikit' );

// Display the template
$gBitSystem->display( 'bitpackage:boards/list_topics.tpl', tra( 'Message Board Threads: ' . $gContent->getField('title') ) );
?>
