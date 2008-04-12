<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/Attic/topic.php,v 1.28 2008/04/12 05:54:07 spiderr Exp $
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

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_boards_read' );


if (isset($_REQUEST["new"])) {
	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
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
	$gBitSystem->verifyPermission( 'p_boards_edit' );

	require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
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
}

if( @BitBase::verifyId( $_REQUEST['b'] ) ) {
} elseif( @BitBase::verifyId( $_REQUEST['migrate_board_id'] ) ) {
	if( $_REQUEST['b'] = BitBoard::lookupByMigrateBoard( $_REQUEST['migrate_board_id'] ) ) {
		bit_redirect( BOARDS_PKG_URL.'index.php?b='. $_REQUEST['b'] );
	}
} else {
	$_REQUEST['b'] = NULL;
}

$gContent = new BitBoard($_REQUEST['b']);
if( !$gContent->load() ) {
	$gBitSystem->fatalError("board id not given");
}

$gContent->verifyViewPermission();

$displayHash = array( 'perm_name' => 'p_boards_read' );
$gContent->invokeServices( 'content_display_function', $displayHash );

/* mass-remove:
the checkboxes are sent as the array $_REQUEST["checked[]"], values are the wiki-PageNames,
e.g. $_REQUEST["checked"][3]="HomePage"
$_REQUEST["submit_mult"] holds the value of the "with selected do..."-option list
we look if any page's checkbox is on and if remove_boards is selected.
then we check permission to delete boards.
if so, we call histlib's method remove_all_versions for all the checked boards.
*/
if( isset( $_REQUEST["submit_mult"] ) && isset( $_REQUEST["checked"] ) && $_REQUEST["submit_mult"] == "remove_boards" ) {

	// Now check permissions to remove the selected bitboard
	$gContent->verifyPermission( 'p_boards_remove' );
	$gBitUser->verifyTicket();

	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['b'] = $_REQUEST['b'];
		$formHash['delete'] = TRUE;
		$formHash['submit_mult'] = 'remove_boards';
		foreach( $_REQUEST["checked"] as $del ) {
			$formHash['input'][] = '<input type="hidden" name="checked[]" value="'.$del.'"/>';
		}
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete '.count( $_REQUEST["checked"] ).' Topics?', 'error' => 'This cannot be undone!' ) );
	} else {
		foreach( $_REQUEST["checked"] as $deleteId ) {
			$deleteComment = new LibertyComment( $deleteId );
			if( $deleteComment->isValid() && $gBitUser->hasPermission('p_liberty_admin_comments') ) {
				if( !$deleteComment->deleteComment() ) {
					$gBitSmarty->assign_by_ref( 'errors', $deleteComment->mErrors );
				}
			}
		}
		if( !empty( $errors ) ) {
			$gBitSmarty->assign_by_ref( 'errors', $errors );
		}
	}
} elseif( isset( $_REQUEST['remove'] ) && BitBase::verifyId( $_REQUEST['thread_id'] ) ) {
	$gBitUser->verifyTicket();
	$tmpTopic = new BitBoardTopic( $_REQUEST['thread_id'] );
	$tmpTopic->load();
	if( !empty( $_REQUEST['cancel'] ) ) {
		// user cancelled - just continue on, doing nothing
	} elseif( empty( $_REQUEST['confirm'] ) ) {
		$formHash['b'] = $_REQUEST['b'];
		$formHash['remove'] = TRUE;
		$formHash['thread_id'] = $_REQUEST['thread_id'];
		$gBitSystem->confirmDialog( $formHash, array( 'warning' => tra( 'Are you sure you want to delete the topic' ).' "'.$tmpTopic->getTitle().'" ?', 'error' => 'This cannot be undone!' ) );
	} else {
		$deleteComment = new LibertyComment($_REQUEST['thread_id']);
		if( $deleteComment->isValid() && $gBitUser->hasPermission('p_liberty_admin_comments') ) {
			if( !$deleteComment->deleteComment() ) {
				$gBitSmarty->assign_by_ref( 'errors', $deleteComment->mErrors );
			}
		}
	}
	
}

$commentsParentId=$gContent->mContentId;
$comments_return_url=  BOARDS_PKG_URL."index.php?b=".urlencode($gContent->mBitBoardId);

require_once (LIBERTY_PKG_PATH.'comments_inc.php');

if( $gBitSystem->isPackageActive( 'switchboard' ) && !empty( $storeComment ) && $gContent->getPreference('boards_mailing_list') ) {
	if( empty( $storeComment->mErrors ) ) {
		global $gSwitchboardSystem;
		require_once( SWITCHBOARD_PKG_PATH.'SwitchboardSystem.php' );
		$email = $gContent->getPreference('boards_mailing_list').'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) );
		$headerHash['mail_from'] = $gBitSystem->getConfig( 'boards_sync_user' ).'@'.$gBitSystem->getConfig( 'boards_sync_mail_server' );
		if( $storeComment->getField( 'user_id' ) == ANONYMOUS_USER_ID ) {
			$headerHash['from_name'] = $storeComment->getField( 'anon_name' );
		} else {
			$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $storeComment->getField( 'user_id', $gBitUser->mUserId ) ) );
			$headerHash['from_name'] = !empty( $userInfo['real_name'] ) ? $userInfo['real_name'] : $userInfo['login'];
			$headerHash['sender'] = $userInfo['email'];
		}
		
		$gSwitchboardSystem->sendEmail( $storeComment->getTitle(), $storeComment->parseData(), $email, $headerHash );
	}
}


// create new bitboard object
$threads = new BitBoardTopic();
$threadList = $threads->getList( $_REQUEST );

$gBitSmarty->assign_by_ref( 'threadList', $threadList );
// getList() has now placed all the pagination information in $_REQUEST['listInfo']
$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );

$gBitSmarty->assign_by_ref( 'board', $gContent );
$gBitSmarty->assign( 'cat_url', BOARDS_PKG_URL."index.php"); //?ct=".urlencode($gContent->mInfo['content_type_guid']));

$gBitThemes->loadAjax( 'mochikit' );

// Display the template
$gBitSystem->display( 'bitpackage:boards/list_topics.tpl', tra( 'Message Board Threads: ' . $gContent->getField('title') ) );
?>
