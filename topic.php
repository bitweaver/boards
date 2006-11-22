<?php
// $Header: /cvsroot/bitweaver/_bit_boards/Attic/topic.php,v 1.9 2006/11/22 12:33:57 squareing Exp $
// Copyright (c) 2004 bitweaver Messageboards
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

$gBitSmarty->assign( 'loadAjax', TRUE );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_bitboards_read' );


if (isset($_REQUEST["new"])) {
	$gBitSystem->verifyPermission( 'p_board_view' );
	require_once( BITBOARDS_PKG_PATH.'lookup_inc.php' );
	$res = true;
	if (isset($_REQUEST["new"]) && is_numeric($_REQUEST["new"])) {
		$res = $gContent->readTopicSet($_REQUEST["new"]);
	}
	if ($res) {
		header ("location: ".$_SERVER['HTTP_REFERER']);
	} else {
		trigger_error(var_export($gContent->mErrors,true ));
	}
	die();
} elseif (isset($_REQUEST["locked"]) || isset($_REQUEST["sticky"])) {
	// Now check permissions to access this page
	$gBitSystem->verifyPermission( 'p_board_edit' );

	require_once( BITBOARDS_PKG_PATH.'lookup_inc.php' );
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
} elseif (!empty($_REQUEST['action'])) {
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
		default:
			break;
	}
} elseif (empty($_REQUEST['b'])) {
	$gBitSystem->fatalError("board id not given");
}


/* mass-remove:
the checkboxes are sent as the array $_REQUEST["checked[]"], values are the wiki-PageNames,
e.g. $_REQUEST["checked"][3]="HomePage"
$_REQUEST["submit_mult"] holds the value of the "with selected do..."-option list
we look if any page's checkbox is on and if remove_bitboards is selected.
then we check permission to delete bitboards.
if so, we call histlib's method remove_all_versions for all the checked bitboards.
*/
if( isset( $_REQUEST["submit_mult"] ) && isset( $_REQUEST["checked"] ) && $_REQUEST["submit_mult"] == "remove_bitboards" ) {

	// Now check permissions to remove the selected bitboard
	$gBitSystem->verifyPermission( 'p_bitboards_remove' );

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['delete'] = TRUE;
		$formHash['submit_mult'] = 'remove_bitboards';
		foreach( $_REQUEST["checked"] as $del ) {
			$formHash['input'][] = '<input type="hidden" name="checked[]" value="'.$del.'"/>';
		}
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete '.count( $_REQUEST["checked"] ).' Threads?', 'error' => 'This cannot be undone!' ) );
	} else {
		foreach( $_REQUEST["checked"] as $deleteId ) {
			$tmpPage = new BitBoardTopic( $deleteId );
			if( !$tmpPage->load() || !$tmpPage->expunge() ) {
				array_merge( $errors, array_values( $tmpPage->mErrors ) );
			}
		}
		if( !empty( $errors ) ) {
			$gBitSmarty->assign_by_ref( 'errors', $errors );
		}
	}
}

$board = new BitBoard($_REQUEST['b']);
$board->load();
$gContent = $board;

$commentsParentId=$board->mContentId;
$comments_return_url=  BITBOARDS_PKG_URL."index.php?b=".urlencode($board->mBitBoardId);

require_once (LIBERTY_PKG_PATH.'comments_inc.php');


// create new bitboard object
$threads = new BitBoardTopic();
$threadList = $threads->getList( $_REQUEST );

$gBitSmarty->assign_by_ref( 'threadList', $threadList );
// getList() has now placed all the pagination information in $_REQUEST['listInfo']
$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );

$gBitSmarty->assign_by_ref( 'board', $board );
$gBitSmarty->assign( 'cat_url', BITBOARDS_PKG_URL."index.php"); //?ct=".urlencode($board->mInfo['content_type_guid']));



// Configure quicktags list
if( $gBitSystem->isPackageActive( 'quicktags' ) ) {
	include_once( QUICKTAGS_PKG_PATH.'quicktags_inc.php' );
}
// Display the template
$gBitSystem->display( 'bitpackage:bitboards/list_topics.tpl', tra( 'Message Boards - Threads' ) );
?>
