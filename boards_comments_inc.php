<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/boards_comments_inc.php,v 1.1 2008/04/12 06:07:44 spiderr Exp $
 * $Id: boards_comments_inc.php,v 1.1 2008/04/12 06:07:44 spiderr Exp $
 *
 * intermediate include file to provide centralized place to pre/post handle comments_inc include
 *
 * @author spider <spider@steelsun.com>
 * @version $Revision: 1.1 $ $Date: 2008/04/12 06:07:44 $ $Author: spiderr $
 * @package boards
 */


require_once (LIBERTY_PKG_PATH.'comments_inc.php');

if( $gBitSystem->isPackageActive( 'switchboard' ) && !empty( $storeComment ) && $gBoard->getPreference('boards_mailing_list') ) {
	if( empty( $storeComment->mErrors ) ) {
		global $gSwitchboardSystem;
		require_once( SWITCHBOARD_PKG_PATH.'SwitchboardSystem.php' );
		$email = $gBoard->getPreference('boards_mailing_list').'@'.$gBitSystem->getConfig( 'boards_email_host', $gBitSystem->getConfig( 'kernel_server_name' ) );
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


?>
