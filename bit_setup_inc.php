<?php
global $gBitSystem, $gBitThemes;

$registerHash = array(
	'package_name' => 'boards',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'boards' ) && $gBitUser->hasPermission( 'p_board_read' )) {
	$menuHash = array(
		'package_name'  => BOARDS_PKG_NAME,
		'index_url'     => BOARDS_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:boards/menu_boards.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	require_once( BOARDS_PKG_PATH.'BitBoard.php' );
	require_once( BOARDS_PKG_PATH.'BitBoardTopic.php' );

	$registerArray = array(
		'content_display_function' => 'boards_content_display',
		'content_preview_function' => 'boards_content_edit',
		'content_edit_function' => 'boards_content_edit',
		'content_store_function' => 'boards_content_store',
		'content_verify_function' => 'boards_content_verify',
		'content_expunge_function' => 'boards_content_expunge',
		'comment_store_function'		=> 'boards_comment_store',
//		'content_view_tpl' => 'bitpackage:boards/service_view_boards.tpl',
		'content_icon_tpl' => 'bitpackage:boards/boards_service_icons.tpl',
		'content_list_sql_function' => 'boards_content_list_sql',
	);

	if ( !$gBitSystem->isFeatureActive( 'boards_hide_edit_tpl' ) &&
		 !$gBitSystem->isFeatureActive( 'boards_link_by_pigeonholes' ) ) {
		$registerArray['content_edit_mini_tpl'] = 'bitpackage:boards/boards_edit_mini_inc.tpl';
	}

	$gLibertySystem->registerService( LIBERTY_SERVICE_FORUMS, BOARDS_PKG_NAME, $registerArray );

	function boards_get_topic_comment( $pThreadForwardSequence ) {
		return( intval( substr( $pThreadForwardSequence, 0, 9 ) ) );
	}

	$gBitThemes->loadCss(BOARDS_PKG_PATH.'styles/boards.css');

	/**
	 * load up moderation in case we are using modcomments
	 * we need to include its bit_setup_inc incase comments gets loaded first
	 */
	if( file_exists(BIT_ROOT_PATH.'moderation/bit_setup_inc.php') ) {
		require_once( BIT_ROOT_PATH.'moderation/bit_setup_inc.php' );
		global $gModerationSystem;
		
		if( $gBitSystem->isPackageActive( 'moderation' ) ) {

			// Register our event handler
			$gModerationSystem->registerModerationObserver(BOARDS_PKG_NAME, 'modcomments', 'board_comments_moderation');
			$gModerationSystem->registerModerationObserver(BOARDS_PKG_NAME, 'liberty', 'board_comments_moderation');
	
			// And define the function we use to handle the observation.
			function board_comments_moderation($pModerationInfo) {
				if( $pModerationInfo['type'] == 'comment_post' ) {
					$storeComment = new LibertyComment( NULL, $pModerationInfo['content_id'] );
					$storeComment->load();
					$comments_return_url = '';
					$root_id = $storeComment->mInfo['root_id'];
					global $gContent;
					$board = new BitBoard(NULL, $root_id);
					$board->load();
					$boardSync = $board->getPreference('board_sync_list_address');
					$code = $storeComment->getPreference('board_confirm_code');
					$approved = $board->getPreference('boards_mailing_list_password');
					// Possible race. Did we beat the cron?
					if( empty($code) ) {
						require_once(BOARDS_PKG_PATH.'admin/boardsync_inc.php');
						// Try to pick up the message!
						board_sync_run(TRUE);
					}
					if( !empty($code) && !empty($boardSync) && !empty($approved) ) {
						$boardSync = str_replace('@', '-request@', $boardSync);
						$code = 'confirm '.$code;
						require_once(KERNEL_PKG_PATH.'BitMailer.php');
						$mailer = new BitMailer();
	
						if( $pModerationInfo['last_status'] == MODERATION_DELETE ) {
							// Send a reject message
							$mailer->sendEmail($code, '', $boardSync,
											   array( 'sender' =>
													  BitBoard::getBoardSyncInbox() ) );
						} else {
							// Send an accept message
							$mailer->sendEmail($code, '', $boardSync,
											   array('sender' =>
													 BitBoard::getBoardSyncInbox(),
													 'x_headers' =>
													 array( 'Approved' =>
															$approved) ) );
						}
					}
				}
			}
		}
	}
}
?>
