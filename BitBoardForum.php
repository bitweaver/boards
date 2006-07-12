<?php
/**
* $Header: /cvsroot/bitweaver/_bit_boards/Attic/BitBoardForum.php,v 1.1 2006/07/12 17:00:32 hash9 Exp $
* $Id: BitBoardForum.php,v 1.1 2006/07/12 17:00:32 hash9 Exp $
*/

/**
* BitBoardBoard class to illustrate best practices when creating a new bitweaver package that
* builds on core bitweaver functionality, such as the Liberty CMS engine
*
* @date created 2004/8/15
* @author spider <spider@steelsun.com>
* @version $Revision: 1.1 $ $Date: 2006/07/12 17:00:32 $ $Author: hash9 $
* @class BitBoardBoard
*/

require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );

class BitBoardForum extends LibertyAttachable {

	/**
	* During initialisation, be sure to call our base constructors
	**/
	function BitBoardForum() {
		LibertyAttachable::LibertyAttachable();
	}

	function loadContent($contentId) {
		global $gBitDb;
		global $gBitUser;
		//var_dump($GLOBALS);
		if( LibertyContent::verifyId( $contentId ) ) {
			// LibertyContent::load()assumes you have joined already, and will not execute any sql!
			// This is a significant performance optimization
			$bindVars = array();
			$selectSql = $joinSql = $whereSql = '';
			array_push( $bindVars, $contentId );
			$gBitUser->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT lc.*, uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name, uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name $selectSql
			FROM `".BIT_DB_PREFIX."liberty_content` lc $joinSql
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON( uue.`user_id` = lc.`modifier_user_id` )
				LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON( uuc.`user_id` = lc.`user_id` )
			WHERE lc.`content_id`=? $whereSql";
			$result = $gBitDb->query( $query, $bindVars );

			$ret = array();
			if( $result && $result->numRows() ) {
				$ret = $result->fields;

				$ret['creator'] =( isset( $result->fields['creator_real_name'] )? $result->fields['creator_real_name'] : $result->fields['creator_user'] );
				$ret['editor'] =( isset( $result->fields['modifier_real_name'] )? $result->fields['modifier_real_name'] : $result->fields['modifier_user'] );
				$ret['display_url'] = BIT_ROOT_URL."index.php?content_id=$contentId";
			}
		}
		return( $ret );
	}

	/**
	* This function generates a list of records from the liberty_content database for use in a list page
	**/
	function getFullList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		$BIT_DB_PREFIX=BIT_DB_PREFIX;
		// this makes sure parameters used later on are set
		LibertyAttachable::prepGetList( $pParamHash );

		$selectSql = $joinSql = $whereSql = '';
		$bindVars = array();
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		// this will set $find, $sort_mode, $max_records and $offset
		extract( $pParamHash );

		if( is_array( $find ) ) {
			// you can use an array of pages
			$whereSql .= " AND lc.`title` IN( ".implode( ',',array_fill( 0,count( $find ),'?' ) )." )";
			$bindVars = array_merge ( $bindVars, $find );
		} elseif( is_string( $find ) ) {
			// or a string
			$whereSql .= " AND UPPER( lc.`title` )like ? ";
			$bindVars[] = '%' . strtoupper( $find ). '%';
		}

		if (empty($pParamHash['ct'])) {
			$query= "SELECT DISTINCT lcom.`root_id` AS root_id, lc.`content_id` AS content_id, lc.`title` AS title, lc.`content_type_guid` AS content_type_guid,
			( SELECT count(*) FROM `".BIT_DB_PREFIX."liberty_comments` slcom WHERE slcom.`root_id`=slcom.`parent_id` AND slcom.`root_id`=lc.`content_id` ) AS post_count
			$selectSql
			FROM ".BIT_DB_PREFIX."liberty_content lc
			INNER JOIN ".BIT_DB_PREFIX."liberty_comments lcom ON( lc.`content_id`=lcom.`root_id` )
			$joinSql
			WHERE TRUE $whereSql
			";

			$query_cant= "SELECT COUNT(DISTINCT lcom.root_id)
			FROM ".BIT_DB_PREFIX."liberty_content lc
			INNER JOIN ".BIT_DB_PREFIX."liberty_comments lcom ON( lc.content_id=lcom.root_id )
			$joinSql
			WHERE TRUE $whereSql
			";
		} else {
			$whereSql .= " AND lc.`content_type_guid`= '{$pParamHash['ct']}'";
			$query= "SELECT lc.`content_id` AS content_id, lc.`title` AS title, lc.`content_type_guid` AS content_type_guid,
			( SELECT count(*) FROM `".BIT_DB_PREFIX."liberty_comments` lcom WHERE lcom.`root_id`=lcom.`parent_id` AND lcom.`root_id`=lc.`content_id` ) AS post_count
			$selectSql
			FROM ".BIT_DB_PREFIX."liberty_content lc
			$joinSql
			WHERE TRUE $whereSql
			";

			$query_cant= "SELECT COUNT(*)
			FROM ".BIT_DB_PREFIX."liberty_content lc
			$joinSql
			WHERE TRUE $whereSql
			";
		}
		$result = $this->mDb->query( $query, $bindVars, $max_records, $offset );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			$res['url']= BITBOARDS_PKG_URL."index.php?c={$res['content_id']}";
			$ret[] = $res;
		}
		$pParamHash["cant"] = $this->mDb->getOne( $query_cant, $bindVars );
		// add all pagination info to pParamHash
		LibertyAttachable::postGetList( $pParamHash );
		return $ret;
	}

	function getForumBoardSelectList() {
		global $gBitDb;
		$query = "SELECT lc.`content_id` as content_id, lc.`title` as title,
			( SELECT count(*)
				FROM `".BIT_DB_PREFIX."forum_map` AS map
				INNER JOIN `".BIT_DB_PREFIX."liberty_comments` lcom ON (map.`topic_content_id` = lcom.`root_id`)
				WHERE lcom.`root_id`=lcom.`parent_id` AND map.`board_content_id`=lc.`content_id`
				) AS post_count
			FROM `".BIT_DB_PREFIX."forum_board` b
			INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON( lc.`content_id` = b.`content_id` )
			ORDER BY  lc.`title` ASC";

		$result = $gBitDb->query( $query);
		$ret = array();
		while( $res = $result->fetchRow() ) {
			$ret[] = $res;
		}
		return $ret;
	}

	/**
	* Generates the URL to the bitboard page
	* @return the link to display the page.
	*/
	function getForumDisplayUrl(&$lcontent) {
		global $gBitDb;
		$ret = NULL;
		if( @$lcontent->verifyId( $lcontent->mContentId ) ) {
			if ($lcontent->mInfo['content_type_guid']=='bitforum') {
				$bId = $gBitDb->getOne("SELECT `board_id` FROM `".BIT_DB_PREFIX."forum_board` s WHERE s.`content_id`=?",array($lcontent->mContentId));
				$ret = BITBOARDS_PKG_URL."index.php?b=$bId";
			} else {
				$ret = BITBOARDS_PKG_URL."index.php?c=".$lcontent->mContentId;
			}
		}
		return $ret;
	}
}
?>
