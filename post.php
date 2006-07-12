<?php
require_once( '../bit_setup_inc.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardForum.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

if (!empty($_REQUEST['action'])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_bitboards_edit' );

	require_once( BITBOARDS_PKG_PATH.'lookup_inc.php' );
	switch ($_REQUEST['action']) {
		case 1:
			// Aprove
			$gContent->mod_approve();
			break;
		case 2:
			// Reject
			$gContent->mod_reject();
			break;
		case 3:
			// Warn
			//$gContent->mod_warn($message);
			break;
		default:
			break;
	}
} elseif (empty($_REQUEST['t'])) {
	$gBitSystem->fatalError("Thread id not given");
}

$gBitSmarty->assign( 'loadAjax', TRUE );

$thread = new BitBoardTopic($_REQUEST['t']);
$thread->load();
$gBitSmarty->assign_by_ref( 'thread', $thread );
if (empty($thread->mInfo['th_root_id'])) {
	$gBitSystem->fatalError(tra( "Invalid thread selection." ) );
}

$board = new BitBoard(null,$thread->mInfo['board_content_id']);
$board->load();
$gBitSmarty->assign_by_ref( 'board', $board );

$commentsParentId=$board->mContentId;
$comments_return_url= BITBOARDS_PKG_URL."index.php?t={$thread->mRootId}";
$gComment = new BitBoardPost($_REQUEST['t']);
$gBitSmarty->assign('comment_template','bitpackage:bitboards/post_display.tpl');

require_once (LIBERTY_PKG_PATH.'comments_inc.php');


// Configure quicktags list
if( $gBitSystem->isPackageActive( 'quicktags' ) ) {
	include_once( QUICKTAGS_PKG_PATH.'quicktags_inc.php' );
}

// WYSIWYG and Quicktag variable
$gBitSmarty->assign( 'textarea_id', 'editbitboards' );

$gBitSystem->display('bitpackage:bitboards/post.tpl', "Show Thread" );


?>
