<?php
$title ="Boards";

$boardsSettings = array(
	'show_avatars' => array(
		'pref' => 'boards_show_avatars',
		'label' => "Show Avatars",
		'type' => "checkbox",
		'default'=> 'y',
		'note' => "",
	),
	'signature' => array(
		'pref' => 'boards_signature',
		'label' => "Board Post Signature",
		'type' => "text",
		'default'=> '',
		'note' => "",
	),
);

foreach( $boardsSettings as $option => $op) {
	if ($op['type']=="checkbox") {
		$editUser->storePreference($op['pref'], !empty( $_REQUEST['boards'][$option]) ? 'y' : 'n', 'users');
	} else {
		$editUser->storePreference($op['pref'], !empty( $_REQUEST['boards'][$option]) ? $_REQUEST['boards'][$option] : '', 'users');
	}
}

$gBitSmarty->assign('boardsSettings',$boardsSettings);

?>