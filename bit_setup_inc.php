<?php
global $gBitSystem;

$registerHash = array(
	'package_name' => 'bitboards',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'bitboards' ) ) {
	$menuHash = array(
		'package_name'  => BITBOARDS_PKG_NAME,
		'index_url'     => BITBOARDS_PKG_URL.'index.php',
		'menu_template' => 'bitpackage:bitboards/menu_bitboards.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );

	require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );
	require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php' );

	$gLibertySystem->registerService( LIBERTY_SERVICE_FORUMS, BITBOARDS_PKG_NAME, array(
		'content_display_function' => 'bitboards_content_display',
		'content_preview_function' => 'bitboards_content_preview',
		'content_edit_function' => 'bitboards_content_edit',
		'content_store_function' => 'bitboards_content_store',
		'content_expunge_function' => 'bitboards_content_expunge',
		'content_edit_mini_tpl' => 'bitpackage:bitboards/bitboards_edit_mini_inc.tpl',
//		'content_view_tpl' => 'bitpackage:bitboards/service_view_boards.tpl',
		'content_icon_tpl' => 'bitpackage:bitboards/bitboards_service_icons.tpl',
		'content_list_sql_function' => 'bitboards_content_list_sql',
	) );
}
?>
