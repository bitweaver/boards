<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_boards/assign.php,v 1.6 2008/08/01 19:18:57 wjames5 Exp $
 * Copyright (c) 2004 bitweaver Messageboards
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
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
$gContent->verifyEditPermission();

if (!empty($_REQUEST['remove'])) {
	foreach ($_REQUEST['remove'] as $board_id => $content_ids) {
		$b = new BitBoard($board_id);
		$b->load();
		if ( $b->hasEditPermission() ){
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
	if ( $b->verifyEditPermission() ){
		foreach( $_REQUEST['assign'] as $content_id ) {
			$b->addContent( $content_id );
		}
	}
}

if (!empty($_REQUEST['integrity'])) {
	$board_id = $_REQUEST['integrity'];
	$b = new BitBoard($board_id);
	$b->load();
	if ( $b->verifyEditPermission() ){
		$b->fixContentMap();
	}
}

$data = BitBoard::getAllMap();
$gBitSmarty->assign_by_ref('data',$data);

// Display the template
$gBitSystem->display( 'bitpackage:boards/board_assign.tpl', tra('Assign content to Board') , array( 'display_mode' => 'display' ));
?>
