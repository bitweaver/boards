<?php
/**
 * @package boards
 * @subpackage functions
 */
$boardsSettings = array(
	/*
	'boards_show_avatars' => array(
		'pref'    => 'boards_show_avatars',
		'label'   => "Show Avatars",
		'type'    => "checkbox",
		'default' => 'y',
		'note'    => "",
	),
	 */
);

if (!empty( $_REQUEST['boards'] ) ) {
	foreach( $boardsSettings as $option => $op) {
		if ($op['type']=="checkbox") {
			$editUser->storePreference($op['pref'], !empty( $_REQUEST['boards'][$option]) ? 'y' : 'n', 'users');
		} else {
			$editUser->storePreference($op['pref'], !empty( $_REQUEST['boards'][$option]) ? $_REQUEST['boards'][$option] : '', 'users');
		}
	}
}

$gBitSmarty->assign('boardsSettings',$boardsSettings);

if( isset( $_REQUEST['bitboarduprefs']['board_id'] ) ) {
	$_REQUEST['b'] = $_REQUEST['bitboarduprefs']['board_id'];
}

$signatureContent= new LibertyContent();
$content_type = $editUser->getPreference('signature_content_type',"");
$content_data = $editUser->getPreference('signature_content_data',"");
if (!empty($content_type) && !empty($content_data)) {
	$signatureContent->mInfo['format_guid']=$editUser->getPreference('signature_content_type');
	$signatureContent->mInfo['data']=$content_data;
}
$gBitSmarty->assignByRef( 'signatureContent', $signatureContent );


if( isset( $_REQUEST["format_guid"] ) ) {
	$signatureContent->mInfo['format_guid'] = $_REQUEST["format_guid"];
}

if( isset( $_REQUEST['bitboarduprefs']["edit"] ) ) {
	$signatureContent->mInfo["data"] = $_REQUEST['bitboarduprefs']["edit"];
}

// If we are in preview mode then preview it!
if( isset( $_REQUEST["preview"] ) ) {
	$gBitSmarty->assign('preview', 'y');
}

// Pro
// Check if the page has changed
if( !empty( $_REQUEST["save_bitboarduprefs"] ) ) {
	// Check if all Request values are delivered, and if not, set them
	// to avoid error messages. This can happen if some features are
	// disabled
	$editUser->storePreference('signature_content_type',$signatureContent->mInfo['format_guid'], 'users');
	$editUser->storePreference('signature_content_data',$signatureContent->mInfo['data'], 'users');
}

?>
