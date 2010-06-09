<?php
/**
 * $Header$
 * $Id$
 *
 * intermediate include file to provide centralized place to pre/post handle comments_inc include
 *
 * @author spider <spider@steelsun.com>
 * @version $Revision$
 * @package boards
 */

/**
 * Initialization
 */
require_once (LIBERTY_PKG_PATH.'comments_inc.php');

if (!function_exists("send_board_email")) {
	function send_board_email($storeComment) {
		global $gBitSystem, $gContent, $gBitUser;

		/*
		 If sync goes both ways we always send and let moderation
		 of the list or the board do its thing. If not we send
		 if the content status says we can.
		*/
		$boardSync = $gContent->getPreference('board_sync_list_address');
		if ( !empty( $boardSync ) ||
			 $storeComment->getContentStatus() > 0 ) {

			require_once( KERNEL_PKG_PATH.'BitMailer.php' );
			$bitMailer = new BitMailer();
			$email = $gContent->getPreference('boards_mailing_list').'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) );
			if( $storeComment->getField( 'user_id' ) == ANONYMOUS_USER_ID ) {
				$headerHash['from_name'] = $storeComment->getField( 'anon_name' );
				$headerHash['from'] = 'anonymous@'.$gBitSystem->getConfig('boards_sync_mail_server');
			} else {
				$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $storeComment->getField( 'user_id', $gBitUser->mUserId ) ) );
				$headerHash['from_name'] = !empty( $userInfo['real_name'] ) ? $userInfo['real_name'] : $userInfo['login'];
				$headerHash['from'] = $userInfo['email'];
				$headerHash['sender'] = $userInfo['email'];
			}
			$headerHash['x_headers']['X-BitBoards-Comment'] = $storeComment->mCommentId;
			$messageId = $bitMailer->sendEmail( $storeComment->getTitle(), $storeComment->parseData(), $email, $headerHash );
			$storeComment->storeMessageId( $messageId );
		}
	}
}

if( !empty( $storeComment ) && $gContent->getPreference('boards_mailing_list') ) {
	if( empty( $storeComment->mErrors ) ) {
		$storeComment->loadComment();
		send_board_email($storeComment);
	}
}

?>
