<?php
/**
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

// we need all three
require_once( BOARDS_PKG_CLASS_PATH.'BitBoard.php' );
require_once( BOARDS_PKG_CLASS_PATH.'BitBoardTopic.php' );
require_once( BOARDS_PKG_CLASS_PATH.'BitBoardPost.php' );

// if we're getting a migrate id then lets move on right away
if( @BitBase::verifyId( $_REQUEST['migrate_topic_id'] ) ) {
	if( $_REQUEST['t'] = BitBoardTopic::lookupByMigrateTopic( $_REQUEST['migrate_topic_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
} elseif( @BitBase::verifyId( $_REQUEST['migrate_post_id'] ) ) {
	if( $_REQUEST['t'] = BitBoardTopic::lookupByMigratePost( $_REQUEST['migrate_post_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?t='. $_REQUEST['t'] );
	}
}

// @TODO move this to edit_post
if (!empty($_REQUEST['action'])) {
	// Now check permissions to access this page
	// @TODO load up the parent board and call verifyUpdatePermission
	$gBitSystem->verifyPermission( 'p_boards_update' );

	$comment = new BitBoardPost($_REQUEST['comment_id']);
	$comment->loadComment();
	if (!$comment->isValid()) {
		$gBitSystem->fatalError( tra("Invalid Comment"), NULL, NULL, HttpStatusCodes::HTTP_GONE );
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

// Finally - load up our topic
$thread = new BitBoardTopic($_REQUEST['t']);
$thread->load();

if( !$thread->isValid() ) {
	$gBitSystem->fatalError( tra("Unknown discussion"), NULL, NULL, HttpStatusCodes::HTTP_GONE );
}

$thread->verifyViewPermission();

// load up the root board we need it
$gBoard = new BitBoard(null,$thread->mInfo['board_content_id']);
$gBoard->load();
$gBitSmarty->assignByRef( 'board', $gBoard );
// force root board to be gContent
$gContent = &$gBoard;
$gBitSmarty->assignByRef('gContent', $gContent);


// if you know what this is please comment it
if (empty($thread->mInfo['th_root_id'])) {
	if ($_REQUEST['action']==3) {
		//Invalid as a result of rejecting the post, redirect to the board
		bit_redirect( $gBoard->getDisplayUrl() );
	} else {
		$gBitSystem->fatalError(tra( "Invalid topic selection." ), NULL, NULL, HttpStatusCodes::HTTP_GONE );
	}
}


// Invoke services
$displayHash = array( 'perm_name' => 'p_boards_read' );
$thread->invokeServices( 'content_display_function', $displayHash );

$thread->readTopic();

$gBitSmarty->assignByRef( 'thread', $thread );
$gBitSmarty->assign( 'topic_locked', BitBoardTopic::isLocked( $thread->mCommentContentId ) );


// Get the thread of comments
$commentsParentId=$thread->mInfo['content_id'];
$comments_return_url= BOARDS_PKG_URL."index.php?t={$thread->mRootId}";
$gBitSystem->setCanonicalLink( BOARDS_PKG_URL.'index.php?t='.$thread->mRootId );
$gComment = new BitBoardPost($_REQUEST['t']);
$gBitSmarty->assign('comment_template','bitpackage:boards/post_display.tpl');

// set default comment display style
if( empty( $_REQUEST["comments_style"] ) ) {
	$_REQUEST["comments_style"] = "flat";
}

require_once( BOARDS_PKG_INCLUDE_PATH.'boards_comments_inc.php' );

if( $gBitUser->isRegistered() ) {
	$postComment['registration_date']=$gBitUser->mInfo['registration_date'];
	$postComment['user_avatar_url']=$gBitUser->mInfo['avatar_url'];
	$postComment['user_url'] = $gBitUser->getDisplayUrl();
}


// display warnings - might be for edit processes - if you know please comment
$warnings = array();
if (!empty($_REQUEST['warning'])) {
	foreach ($_REQUEST['warning'] as $id => $state) {
		if (strcasecmp($state,'show')==0) {
			$warnings[$id]=true;
		}
	}
}
$gBitSmarty->assignByRef('warnings',$warnings);


// ajax support
$gBitThemes->loadAjax( 'mochikit' );

$gBitSystem->display('bitpackage:boards/list_posts.tpl', "Show Thread: " . $thread->getField('title') , array( 'display_mode' => 'display' ));
?>
