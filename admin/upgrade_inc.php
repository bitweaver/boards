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
require_once( BITBOARDS_PKG_PATH.'admin/phpbb_upgrade.php' );

migrate_phpbb();


?>
