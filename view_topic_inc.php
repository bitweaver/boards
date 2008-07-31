<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );

$gBitSystem->verifyPermission( 'p_boards_read' );

if (!empty($_REQUEST['action'])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_boards_edit' );

	$comment = new BitBoardPost($_REQUEST['comment_id']);
	$comment->loadComment();
	if (!$comment->isValid()) {
		$gBitSystem->setHttpStatus( 404 );
		$gBitSystem->fatalError("Invalid Comment");
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
		bit_redirect( BOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
} elseif( @BitBase::verifyId( $_REQUEST['migrate_post_id'] ) ) {
	if( $_REQUEST['t'] = BitBoardTopic::lookupByMigratePost( $_REQUEST['migrate_post_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
}

$gBitThemes->loadAjax( 'mochikit' );

$thread = new BitBoardTopic($_REQUEST['t']);
if( !$thread->load() ) {
	$gBitSystem->setHttpStatus( 404 );
	$gBitSystem->fatalError("Unkown discussion");
}

$thread->verifyViewPermission();

$displayHash = array( 'perm_name' => 'p_boards_read' );
$thread->invokeServices( 'content_display_function', $displayHash );

if (empty($thread->mInfo['th_root_id'])) {
	if ($_REQUEST['action']==3) {
		//Invalid as a result of rejecting the post, redirect to the board
		$tb = new BitBoard(null,$thread->mInfo['board_content_id']);
		header("Location: ".$tb->getDisplayUrl());
	} else {
		$gBitSystem->setHttpStatus( 404 );
		$gBitSystem->fatalError(tra( "Invalid topic selection." ) );
	}
}
$thread->readTopic();

$gBitSmarty->assign('topic_locked',$thread->isLocked());

$gBitSmarty->assign_by_ref( 'thread', $thread );
$gBoard = new BitBoard(null,$thread->mInfo['board_content_id']);
$gBoard->load();
$gBitSmarty->assign_by_ref( 'board', $gBoard );
$gContent = &$gBoard;
$gBitSmarty->assign_by_ref('gContent', $gContent);

$commentsParentId=$thread->mInfo['content_id'];
$comments_return_url= BOARDS_PKG_URL."index.php?t={$thread->mRootId}";
$gComment = new BitBoardPost($_REQUEST['t']);
$gBitSmarty->assign('comment_template','bitpackage:boards/post_display.tpl');

if( empty( $_REQUEST["comments_style"] ) ) {
	$_REQUEST["comments_style"] = "flat";
}

require_once( BOARDS_PKG_PATH.'boards_comments_inc.php' );

if( !empty( $_REQUEST['remove'] ) && @BitBase::verifyId( $_REQUEST['t'] ) ) {
	$gBitUser->verifyTicket();
	$boardId = $thread->getField( 'board_id' );
	if( $gComment->expunge() && $thread->expunge() ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?b='.$boardId );
	}
}

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

// Invoke services
$gComment->invokeServices( 'content_display_function' );

$gBitSystem->display('bitpackage:boards/list_posts.tpl', "Show Thread: " . $thread->getField('title') , array( 'display_mode' => 'display' ));


?>
