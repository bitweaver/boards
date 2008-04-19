<?php
// $Header: /cvsroot/bitweaver/_bit_boards/mailing_list.php,v 1.2 2008/04/16 21:14:51 spiderr Exp $
// Copyright (c) bitweaver Group
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

// Initialization
require_once( '../bit_setup_inc.php' );
require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BOARDS_PKG_PATH.'BitBoard.php' );
require_once( BOARDS_PKG_PATH.'lookup_inc.php' );
require_once( UTIL_PKG_PATH.'mailman_lib.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

// Now check permissions to access this page
$gContent->verifyViewPermission();

if( $gBitSystem->getConfig('boards_sync_user') ) {
	$boardSyncInbox = $gBitSystem->getConfig('boards_sync_user').'@'.$gBitSystem->getConfig('boards_sync_mail_server');
	$gBitSmarty->assign( 'boardSyncInbox', $boardSyncInbox );
}

if( !empty( $_REQUEST['create_list'] ) ) {
	//------ Email List ------//
	if( !($error = mailman_newlist( array( 'listname' => $_REQUEST['boards_mailing_list'], 'admin-password'=>$_REQUEST['boards_mailing_list_password'], 'listadmin-addr'=>$gBitUser->getField( 'email' ) ) )) ) {
		$gContent->storePreference( 'boards_mailing_list', !empty( $_REQUEST['boards_mailing_list'] ) ? $_REQUEST['boards_mailing_list'] : NULL );
		$gContent->storePreference( 'boards_mailing_list_password', $_REQUEST['boards_mailing_list_password'] );
	} else {
		$gBitSmarty->assign( 'errorMsg', $error );
	}

//		if( $gContent->getPreference( 'boards_mailing_list' ) && $_REQUEST['boards_mailing_list'] != $gContent->getPreference( 'boards_mailing_list' ) ) {
			// Name change
//			groups_mailman_rename( $gContent->getPreference( 'boards_mailing_list' ), $_REQUEST['boards_mailing_list'] );
//		}

} elseif( !empty( $_REQUEST['delete_list'] ) ) {
	if( $gContent->getPreference( 'boards_mailing_list' ) ) {
		if( empty( $_REQUEST['confirm'] ) ) {
			$formHash['delete_list'] = TRUE;
			$formHash['b'] = $gContent->getField( 'board_id' );
			$gBitSystem->confirmDialog( $formHash, array( 'warning' => 'Are you sure you want to delete the mailing list '.$gContent->getTitle().'?', 'error' => 'This cannot be undone!' ) );
		} else {
			if( !($error = mailman_rmlist( $gContent->getPreference( 'boards_mailing_list' ) )) ) {
				$gContent->storePreference( 'boards_mailing_list', NULL );
				$gContent->storePreference( 'boards_mailing_list_password', NULL );
				header( "Location: ".BOARDS_PKG_URL."mailing_list.php?b=".$gContent->getField( 'board_id' ) );
			} else {
				$gBitSmarty->assign( 'errorMsg', $error );
			}
		}
	}
} elseif( !empty( $_REQUEST['save_list_address'] ) ) {
	$gContent->storePreference( 'board_sync_list_address', (!empty( $_REQUEST['board_sync_list_address'] ) ? $_REQUEST['board_sync_list_address'] : NULL ) );
} elseif( $gContent->getPreference( 'boards_mailing_list' ) ) {
	// check for submits that need boards_mailing_list
	if( !empty( $_REQUEST['subscribe_boardsync'] ) ) {
		if( $gContent->getPreference('board_sync_list_address') ) {
			mailman_addmember( $gContent->getPreference( 'boards_mailing_list' ), $boardSyncInbox );
		}
	} elseif( !empty( $_REQUEST['unsubscribe_boardsync'] ) ) {
		if( $gContent->getPreference('board_sync_list_address') ) {
			mailman_remove_member( $gContent->getPreference( 'boards_mailing_list' ), $boardSyncInbox );
		}
	} elseif( !empty( $_REQUEST['subscribe'] ) ) {
		mailman_addmember( $gContent->getPreference( 'boards_mailing_list' ), $gBitUser->getField( 'email' ) );
	} elseif( !empty( $_REQUEST['unsubscribe'] ) ) {
		mailman_remove_member( $gContent->getPreference( 'boards_mailing_list' ), $gBitUser->getField( 'email' ) );
	}
}

if( $gContent->getPreference( 'boards_mailing_list' ) ) {
	$gBitSmarty->assign( 'boardsMailingList', $gContent->getPreference( 'boards_mailing_list' ).'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) ) );
	if ( $gContent->hasUserPermission( 'p_boards_boards_members_view' ) ){
		$members = mailman_list_members( $gContent->getPreference( 'boards_mailing_list' ) );
		$gBitSmarty->assign_by_ref( 'listMembers', $members );
	}
} else {
	$gBitSmarty->assign( 'suggestedListName', preg_replace( '/[^a-z0-9]/', '', strtolower( $gContent->getTitle() ) ) );
}

// display
$gBitSmarty->assign_by_ref( 'board', $gContent );
$gBitSystem->display( "bitpackage:boards/mailing_list.tpl", $gContent->getTitle() ." ".  tra( 'Message Board Mailing List' ) );
?>