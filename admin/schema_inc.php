<?php
$tables = array(
	'boards_posts' => "
		comment_id I4 PRIMARY,
		is_approved I1 NOTNULL DEFAULT(0),
		is_warned I1 NOTNULL DEFAULT(0),
		warned_message X,
		migrate_post_id INT
		CONSTRAINT ', CONSTRAINT `boards_posts_comment_ref` FOREIGN KEY (`comment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_comments` (`comment_id`)'
	",
	'boards_topics' => "
		parent_id I4 PRIMARY,
		is_locked I1 NOTNULL DEFAULT(0),
		is_moved I4 NOTNULL DEFAULT(0),
		is_sticky I1 NOTNULL DEFAULT(0),
		migrate_topic_id INT
		CONSTRAINT ', CONSTRAINT `boards_topics_parent_ref` FOREIGN KEY (`parent_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
	",
	'boards' => "
		board_id I4 PRIMARY,
		content_id I4 NOTNULL,
		migrate_board_id INT
		CONSTRAINT ', CONSTRAINT `boards_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
	",
	'boards_map' => "
		board_content_id I4 NOTNULL,
		topic_content_id I4 PRIMARY
		CONSTRAINT ', CONSTRAINT `boards_topics_boards_ref` FOREIGN KEY (`board_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
					, CONSTRAINT `boards_topics_related_ref` FOREIGN KEY (`topic_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
	",
	'boards_tracking' => "
		user_id I4 NOTNULL,
		topic_id C(10),
		track_date I4 NOTNULL DEFAULT(0),
		notify I1 NOTNULL DEFAULT(0),
		notify_date I4 NOTNULL DEFAULT(0)
	"
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( BOARDS_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( BOARDS_PKG_NAME, array(
	'description' => "Highly integrated message boards package.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Indexes
$indices = array(
	'boards_id_idx' => array('table' => 'boards', 'cols' => 'board_id', 'opts' => NULL ),
);
$gBitInstaller->registerSchemaIndexes( BOARDS_PKG_NAME, $indices );

// ### Sequences
$sequences = array (
	'boards_board_id_seq' => array( 'start' => 1 ),
);
$gBitInstaller->registerSchemaSequences( BOARDS_PKG_NAME, $sequences );

// ### Default UserPermissions
$gBitInstaller->registerUserPermissions( BOARDS_PKG_NAME, array(
	array( 'p_boards_admin' , 'Can admin message boards'  , 'admin'  , BOARDS_PKG_NAME ),
	array( 'p_boards_create', 'Can create a message board', 'editors', BOARDS_PKG_NAME ),
	array( 'p_boards_edit'  , 'Can edit any message board', 'editors', BOARDS_PKG_NAME ),
	array( 'p_boards_read'  , 'Can read message boards'   , 'basic'  , BOARDS_PKG_NAME ),
	array( 'p_boards_remove', 'Can delete message boards' , 'editors', BOARDS_PKG_NAME ),
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( BOARDS_PKG_NAME, array(
	array( BOARDS_PKG_NAME, 'boards_thread_track', 'y' ),
));
if(defined('RSS_PKG_NAME')) {
	$gBitInstaller->registerPreferences( BOARDS_PKG_NAME, array(
		array( RSS_PKG_NAME, BOARDS_PKG_NAME.'_rss', 'y'),
	));
}
?>
