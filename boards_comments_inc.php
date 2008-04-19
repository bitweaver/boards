<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/boards_comments_inc.php,v 1.3 2008/04/19 18:11:55 spiderr Exp $
 * $Id: boards_comments_inc.php,v 1.3 2008/04/19 18:11:55 spiderr Exp $
 *
 * intermediate include file to provide centralized place to pre/post handle comments_inc include
 *
 * @author spider <spider@steelsun.com>
 * @version $Revision: 1.3 $ $Date: 2008/04/19 18:11:55 $ $Author: spiderr $
 * @package boards
 */


require_once (LIBERTY_PKG_PATH.'comments_inc.php');

if( !empty( $storeComment ) && $gBoard->getPreference('boards_mailing_list') ) {
	if( empty( $storeComment->mErrors ) ) {
		$storeComment->loadComment();
		require_once( KERNEL_PKG_PATH.'BitMailer.php' );
		$bitMailer = new BitMailer();
		$email = $gBoard->getPreference('boards_mailing_list').'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) );
		$headerHash['mail_from'] = $gBitSystem->getConfig( 'boards_sync_user' ).'@'.$gBitSystem->getConfig( 'boards_sync_mail_server' );
		if( $storeComment->getField( 'user_id' ) == ANONYMOUS_USER_ID ) {
			$headerHash['from_name'] = $storeComment->getField( 'anon_name' );
		} else {
			$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $storeComment->getField( 'user_id', $gBitUser->mUserId ) ) );
			$headerHash['from_name'] = !empty( $userInfo['real_name'] ) ? $userInfo['real_name'] : $userInfo['login'];
			$headerHash['sender'] = $userInfo['email'];
		}
		$headerHash['x_headers']['X-BitBoards-Comment'] = $storeComment->mCommentId;
		$messageId = $bitMailer->sendEmail( $storeComment->getTitle(), $storeComment->parseData(), $email, $headerHash );
		$storeComment->storeMessageId( $messageId );
	}
}


?>
