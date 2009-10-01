<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/assign.php,v 1.9 2009/10/01 14:16:58 wjames5 Exp $
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
 * @package boards
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'boards' );

require_once(BOARDS_PKG_PATH.'lookup_inc.php' );

// verify minimal edit permission level
$gContent->verifyUpdatePermission();

if (!empty($_REQUEST['remove'])) {
	foreach ($_REQUEST['remove'] as $board_id => $content_ids) {
		$b = new BitBoard($board_id);
		$b->load();
		if ( $b->hasUpdatePermission() ){
			foreach ($content_ids as $content_id => $remove) {
				if ($remove) {
					$b->removeContent($content_id);
				}
			}
		}else{
			// @TODO assign error and report back to user which were not processed
		}
	}
}

if( !empty( $_REQUEST['assign'] ) && @BitBase::verifyId( $_REQUEST['to_board_id'] ) ) {
	$b = new BitBoard( $_REQUEST['to_board_id'] );
	$b->load();
	if ( $b->verifyUpdatePermission() ){
		foreach( $_REQUEST['assign'] as $content_id ) {
			$b->addContent( $content_id );
		}
	}
}

if (!empty($_REQUEST['integrity'])) {
	$board_id = $_REQUEST['integrity'];
	$b = new BitBoard($board_id);
	$b->load();
	if ( $b->verifyUpdatePermission() ){
		$b->fixContentMap();
	}
}

$data = BitBoard::getAllMap();
$gBitSmarty->assign_by_ref('data',$data);

// Display the template
$gBitSystem->display( 'bitpackage:boards/board_assign.tpl', tra('Assign content to Board') , array( 'display_mode' => 'display' ));
?>
