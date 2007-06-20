<?php
global $gBitSystem;

$registerHash = array(
	'package_name' => 'boards',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'boards' ) ) {
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
		'content_preview_function' => 'boards_content_preview',
		'content_edit_function' => 'boards_content_edit',
		'content_store_function' => 'boards_content_store',
		'content_expunge_function' => 'boards_content_expunge',
//		'content_view_tpl' => 'bitpackage:boards/service_view_boards.tpl',
		'content_icon_tpl' => 'bitpackage:boards/boards_service_icons.tpl',
		'content_list_sql_function' => 'boards_content_list_sql',
	);

	if ( $gBitSystem->isFeatureActive( 'boards_hide_edit_tpl' ) ) {
		$registerArray['content_edit_mini_tpl'] = 'bitpackage:boards/boards_edit_mini_inc.tpl';
	}

	$gLibertySystem->registerService( LIBERTY_SERVICE_FORUMS, BOARDS_PKG_NAME, $registerArray );

	function boards_get_topic_comment( $pThreadForwardSequence ) {
		return( intval( substr( $pThreadForwardSequence, 0, 9 ) ) );
	}
}
?>
