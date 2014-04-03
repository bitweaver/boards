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

// if we're getting a migrate id then lets move on right away
if ( @BitBase::verifyId( $_REQUEST['migrate_board_id'] ) ) {
	require_once( BOARDS_PKG_PATH.'BitBoard.php' );

	if( $_REQUEST['b'] = BitBoard::lookupByMigrateBoard( $_REQUEST['migrate_board_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?b='. $_REQUEST['b'] );
	}
}

// Load up the board
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );

if( !$gContent->isValid() ) {
	$gBitSystem->fatalError( "The board you requested could not be found. <a href='".BOARDS_PKG_URL."'>View all boards</a>", NULL, NULL, HttpStatusCodes::HTTP_GONE );
}

// approve or reject ananymous comments
if (!empty($_REQUEST['action'])) {
	// Check edit perms on the group
	$gContent->verifyUpdatePermission();
	
	// Check the ticket
	$gBitUser->verifyTicket();

	// Load up the comment as a board post
	require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
	$comment = new BitBoardPost($_REQUEST['comment_id']);
	$comment->loadComment();

	if (!$comment->isValid()) {
		$gBitSystem->fatalError("Invalid Comment Id");
	}
	
	// Take action
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
}else{
	// we're just here to view (in most cases) so make sure we can
	$gContent->verifyViewPermission();
}

/* One more thing before we get into displaying
 * A mass remove topics request might be made, handle it, perms are checked in the include, it does not require board edit perms necessarily.
 * Code is moved to edit_topic_inc to try to make this all a little more sane.
 *
 * @TODO perhaps move this into the action process above
 */
require_once( BOARDS_PKG_PATH.'edit_topic_inc.php' );


// Ok finally we can get on with viewing our board

// liberty display services
$displayHash = array( 'perm_name' => 'p_boards_read' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// set some comment values since topics are comments
$commentsParentId=$gContent->mContentId;
$comments_return_url=  BOARDS_PKG_URL."index.php?b=".urlencode($gContent->mBitBoardId);

// @TODO not clear why we load up comments and topics after this when its likely to get both. If someone figures it out please clarify.
require_once( BOARDS_PKG_PATH.'boards_comments_inc.php' );

// get the topics for this board
require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );

$threads = new BitBoardTopic( $gContent->mContentId );

// lets pass in a ref to the root obj so we can fully mimic comments
$threads->mRootObj = $gContent; 
$threadsListHash = $_REQUEST;
$threadList = $threads->getList( $threadsListHash );

$gBitSmarty->assign_by_ref( 'threadList', $threadList );

// getList() has now placed all the pagination information in $_REQUEST['listInfo']
$gBitSmarty->assign_by_ref( 'listInfo', $threadsListHash['listInfo'] );

$gBitSmarty->assign_by_ref( 'board', $gContent );
$gBitSmarty->assign( 'cat_url', BOARDS_PKG_URL."index.php"); //?ct=".urlencode($gContent->mInfo['content_type_guid']));

$gBitThemes->loadAjax( 'mochikit' );

// Display the template
$gBitSystem->display( 'bitpackage:boards/list_topics.tpl', tra( 'Message Board Threads: ' . $gContent->getField('title') ) , array( 'display_mode' => 'display' ));
?>
