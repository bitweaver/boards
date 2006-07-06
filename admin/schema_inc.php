<?php
$tables = array(
	'forum_post' => "
		comment_id I4 PRIMARY,
		unreg_uname C(80),
		approved I1 NOTNULL DEFAULT(0),
		deleted I1 NOTNULL DEFAULT(0),
		warned I1 NOTNULL DEFAULT(0),
		warned_content_id I4 NULL
	",
	'forum_thread' => "
		parent_id I4 PRIMARY,
		locked I1 NOTNULL DEFAULT(0),
		moved I4 NOTNULL DEFAULT(0),
		deleted I1 NOTNULL DEFAULT(0),
		sticky I1 NOTNULL DEFAULT(0)
	",
);

global $gBitInstaller;

$gBitInstaller->makePackageHomeable( BITBOARDS_PKG_NAME );

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( BITBOARDS_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( BITBOARDS_PKG_NAME, array(
	'description' => "BitForum package to demonstrate how to build a bitweaver package.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Indexes
$indices = array(
	'bit_bitboards_bitforum_id_idx' => array('table' => 'bitforums', 'cols' => 'bitforum_id', 'opts' => NULL ),
);
$gBitInstaller->registerSchemaIndexes( BITBOARDS_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'bitboards_id_seq' => array( 'start' => 1 ),
	'bitboards_topic_id_seq' => array( 'start' => 1 ),
);
$gBitInstaller->registerSchemaSequences( BITBOARDS_PKG_NAME, $sequences );



$gBitInstaller->registerSchemaDefault( BITBOARDS_PKG_NAME, array(
	//      "INSERT INTO `".BIT_DB_PREFIX."bit_bitboards_types` (`type`) VALUES ('BitForum')",
) );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( BITBOARDS_PKG_NAME, array(
	array( 'p_bitboards_admin', 'Can admin bitforum', 'admin', BITBOARDS_PKG_NAME ),
	array( 'p_bitboards_create', 'Can create a bitforum', 'admin', BITBOARDS_PKG_NAME ),
	array( 'p_bitboards_edit', 'Can edit any bitforum', 'admin', BITBOARDS_PKG_NAME ),
	array( 'p_bitboards_read', 'Can read bitforum', 'basic',  BITBOARDS_PKG_NAME ),
	array( 'p_bitboards_remove', 'Can delete bitforum', 'admin',  BITBOARDS_PKG_NAME ),
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( BITBOARDS_PKG_NAME, array(
	array( BITBOARDS_PKG_NAME, 'bitboards_default_ordering', 'bitforum_id_desc' ),
	array( BITBOARDS_PKG_NAME, 'bitboards_list_bitforum_id', 'y' ),
	array( BITBOARDS_PKG_NAME, 'bitboards_list_title', 'y' ),
	array( BITBOARDS_PKG_NAME, 'bitboards_list_description', 'y' ),
	array( BITBOARDS_PKG_NAME, 'bitboards_list_bitforums', 'y' ),
) );
?>
