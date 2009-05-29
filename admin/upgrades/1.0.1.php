<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_boards/admin/upgrades/1.0.1.php,v 1.1 2009/05/29 16:03:49 spiderr Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => BOARDS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Add boards sections and positioning.",
	'post_upgrade' => NULL,
);
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	array( 'CREATE' => array(
		'boards_sections' => "
			section_id I4 PRIMARY,
			section_title C(255)
		",	
	)),
	// insert new column
	array( 'ALTER' => array(
		'boards' => array(
			'section_id' => array( '`section_id`', 'I4' ),
			'pos' => array( '`pos`', 'I4' ),
	))),
	array( 'CREATEINDEX' => array(
		'boards_sections_idx'       => array( 'boards', 'section_id', array() ),
	)),
	array( 'CREATESEQUENCE' => array(
		'boards_sections_id_seq',
	)),
)),

));
?>
