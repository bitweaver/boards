<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

if (!empty($_REQUEST['action'])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_bitboards_edit' );

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
		case 3:
			//Moderate
			$comment->loadMetaData();
			$comment->modWarn($_REQUEST['warning_message']);
		default:
			break;
	}
}

if( @BitBase::verifyId( $_REQUEST['t'] ) ) {
	// nothing for now
} elseif( @BitBase::verifyId( $_REQUEST['migrate_topic_id'] ) ) {
	if( $_REQUEST['t'] = BitBoardTopic::lookupByMigrateTopic( $_REQUEST['migrate_topic_id'] ) ) {
		bit_redirect( BITBOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
} elseif( @BitBase::verifyId( $_REQUEST['migrate_post_id'] ) ) {
	if( $_REQUEST['t'] = BitBoardTopic::lookupByMigratePost( $_REQUEST['migrate_post_id'] ) ) {
		bit_redirect( BITBOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
}

$gBitSystem->verifyPermission( 'p_bitboards_read' );

$gBitSmarty->assign( 'loadAjax', 'prototype' );

$thread = new BitBoardTopic($_REQUEST['t']);
if( !$thread->load() ) {
	$gBitSystem->fatalError("Thread id not given");
}

if (empty($thread->mInfo['th_root_id'])) {
	if ($_REQUEST['action']==3) {
		//Invalid as a result of rejecting the post, redirect to the board
		$tb = new BitBoard(null,$thread->mInfo['board_content_id']);
		header("Location: ".$tb->getDisplayUrl());
	} else {
		$gBitSystem->fatalError(tra( "Invalid topic selection." ) );
	}
}
$thread->readTopic();

$gBitSmarty->assign('topic_locked',$thread->isLocked());

$gBitSmarty->assign_by_ref( 'thread', $thread );
$board = new BitBoard(null,$thread->mInfo['board_content_id']);
$board->load();
$gBitSmarty->assign_by_ref( 'board', $board );

$commentsParentId=$thread->mInfo['content_id'];
$comments_return_url= BITBOARDS_PKG_URL."index.php?t={$thread->mRootId}";
$gComment = new BitBoardPost($_REQUEST['t']);
$gBitSmarty->assign('comment_template','bitpackage:bitboards/post_display.tpl');

if( empty( $_REQUEST["comments_style"] ) ) {
	$_REQUEST["comments_style"] = "flats";
}

require_once (LIBERTY_PKG_PATH.'comments_inc.php');

$postComment['registration_date']=$gBitUser->mInfo['registration_date'];
$postComment['user_avatar_url']=$gBitUser->mInfo['avatar_url'];
$postComment['user_url'] = $gBitUser->getDisplayUrl();

$warnings = array();
if (!empty($_REQUEST['warning'])) {
	foreach ($_REQUEST['warning'] as $id => $state) {
		if (strcasecmp($state,'show')==0) {
			$warnings[$id]=true;
		}
	}
}
$gBitSmarty->assign_by_ref('warnings',$warnings);

// Configure quicktags list
if( $gBitSystem->isPackageActive( 'quicktags' ) ) {
	include_once( QUICKTAGS_PKG_PATH.'quicktags_inc.php' );
}

// WYSIWYG and Quicktag variable
$gBitSmarty->assign( 'textarea_id', 'editbitboards' );

$gBitSystem->display('bitpackage:bitboards/list_posts.tpl', "Show Thread: " . $thread->getField('title') );


?>
