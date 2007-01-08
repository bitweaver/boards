<?php

// Just a few phpBB migration queries for now....

/*
-- POSTGRESQL-centric initial SQL
INSERT INTO liberty_content ( content_id, title, data, content_type_guid, format_guid, content_status_id, user_id, modifier_user_id, created, last_modified ) (SELECT nextval('liberty_content_id_seq'), forum_name, forum_desc, 'bitboard', 'bbcode', 50, 1, 1, CURRENT_TIMESTAMP::abstime::int::bigint, CURRENT_TIMESTAMP::abstime::int::bigint FROM phpbb.forums); 

INSERT INTO boards (board_id, content_id) (SELECT forum_id, content_id FROM phpbb.forums INNER JOIN liberty_content ON (content_type_guid='bitboard' AND forum_name=title) );
ALTER SEQUENCE boards_board_id_seq RESTART WITH 6;
-- OR: INSERT INTO boards (board_id, content_id) (SELECT nextval('boards_board_id_seq'), content_id FROM liberty_content WHERE content_type_guid='bitboard');


INSERT INTO boards_map (board_content_id, topic_content_id) (SELECT content_id, content_id FROM phpbb.forums INNER JOIN liberty_content ON (content_type_guid='bitboard' AND forum_name=title) );
*/

require_once( '../../bit_setup_inc.php' );

$_SESSION['captcha_verified'] = TRUE;

global $db;

if( file_exists( PHPBB_PKG_PATH.'config.php' ) ) {
	require_once( PHPBB_PKG_PATH.'config.php' );
}

chdir( PHPBB_PKG_PATH );
define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/bbcode.'.$phpEx);

migrate_phpbb();

function migrate_phpbb() {
	global $gBitDb, $db;

	if( $forumIds = $gBitDb->getAssoc( "SELECT `forum_id`,`content_id` FROM " . FORUMS_TABLE . " bbf INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_type_guid`='bitboard' AND bbf.`forum_name`=lc.`title`) ORDER BY bbf.forum_id" ) ) {
		foreach( $forumIds as $forumId => $contentId ) {
			migrate_phpbb_forum( $forumId, $contentId );
		}
		die;
	}
}

function migrate_phpbb_forum( $pForumId, $pForumContentId  ) {
	global $db;
	$sql = "SELECT * FROM " . TOPICS_TABLE . " bbt 
				INNER JOIN " . POSTS_TABLE . " bbp ON(bbt.topic_first_post_id=bbp.post_id)  
				INNER JOIN " . POSTS_TEXT_TABLE . " bbpt ON(bbpt.post_id=bbp.post_id)  
			WHERE bbt.forum_id=$pForumId
			ORDER BY bbt.topic_id LIMIT 10";
	if ( !($result = $db->sql_query($sql)) ) {
		message_die(GENERAL_ERROR, "Could not obtain topic/post information.", '', __LINE__, __FILE__, $sql);
	}
	while ( $row = $db->sql_fetchrow($result) ) {
		$commentHash = array();
		$commentHash['root_id'] = $pForumContentId;
		$commentHash['parent_id'] = $pForumContentId;
		$commentHash['anon_name'] = $row['post_username'];
		$commentHash['title'] = $row['post_subject'];
		$commentHash['edit'] = $row['post_text'];
		$commentHash['format_guid'] = 'bbcode';
		$commentHash['created'] = $row['post_time'];
		$commentHash['last_modified'] = $row['post_edit_time'];
		$commentHash['user_id'] = $row['poster_id'];
		$commentHash['ip'] = decode_ip( $row['poster_ip'] );
		$rootComment = new LibertyComment();
//$rootComment->mDb->StartTrans();
		print "Migrating Topic $row[topic_id]<br/>\n";
		if( $rootComment->storeComment( $commentHash ) ) {
print "Migrating Post $row[post_id]<br/>\n";
			$topicHash['root_id'] = $rootComment->mContentId;
			$topicHash['is_moved'] = $row['topic_moved_id'];
			$topicHash['is_sticky'] = !empty( $row['topic_type'] ) ? '1' : NULL;
			$topicHash['is_moved'] = ($row['topic_status'] == 2 ? '1' : NULL);
			$topicHash['migrate_topic_id'] = $row['topic_id'];
			$rootTopic = new BitBoardTopic( $rootComment->mContentId );
			$rootTopic->store( $topicHash );
			migrate_phpbb_topic( $row['topic_id'], $rootComment );
		} else {
			vd( $commentHash );
			vd( $rootComment->mErrors );
		}
die;
	}
	$db->sql_freeresult($result);
}

function migrate_phpbb_topic( $pTopicId, &$pRootComment ) {
	global $db;
	$sql = "SELECT * FROM " . POSTS_TABLE . " bbp
				INNER JOIN " . POSTS_TEXT_TABLE . " bbpt ON(bbpt.post_id=bbp.post_id)  
			WHERE bbp.topic_id=$pTopicId
			ORDER BY bbp.post_time ";
	if ( !($result = $db->sql_query($sql)) ) {
		message_die(GENERAL_ERROR, "Could not obtain topic/post information.", '', __LINE__, __FILE__, $sql);
	}
	while ( $row = $db->sql_fetchrow($result) ) {
print "Migrating Post $row[post_id]<br/>\n";
		$commentHash = array();
		$commentHash['root_id'] = $pRootComment->getField( 'root_id' );
		$commentHash['parent_id'] = $pRootComment->getField( 'parent_id' );
		$commentHash['anon_name'] = $row['post_username'];
		$commentHash['title'] = $row['post_subject'];
		$commentHash['edit'] = $row['post_text'];
		$commentHash['format_guid'] = 'bbcode';
		$commentHash['created'] = $row['post_time'];
		$commentHash['last_modified'] = $row['post_edit_time'];
		$commentHash['user_id'] = $row['poster_id'];
		$commentHash['ip'] = decode_ip( $row['poster_ip'] );
		$newComment = new LibertyComment();
$newComment->mDb->StartTrans();
		if( $newComment->storeComment( $commentHash ) ) {
			$postHash['migrate_topic_id'] = $row['post_id'];
			$newPost = new BitBoardPost( $newComment->mCommentId );
			$newPost->store( $postHash );
		} else {
			vd( $commentHash );
			vd( $newComment->mErrors );
		}
$newComment->mDb->CompleteTrans();
	}
}

?>
