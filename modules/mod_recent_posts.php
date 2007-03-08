<?php
/**
 * Params: 
 * - title : if is "title", show the title of the post, else show the date of creation
 *
 * @version $Header: /cvsroot/bitweaver/_bit_boards/modules/mod_recent_posts.php,v 1.1 2007/03/08 03:27:55 spiderr Exp $
 * @package boards
 * @subpackage modules
 */

/**
 * required setup
 */

include_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );

global $gBitSmarty, $gQueryUserId, $module_rows, $module_params, $gBitSystem;

$listHash = array( 'user_id' => $gQueryUserId, 'sort_mode' => 'created_desc', 'max_records' => $module_rows );
$post = new BitBoardPost();
if( $postList = $post->getList( $listHash ) ) {
	$gBitSmarty->assign('modLastBoardPosts', $postList );
}

$gBitThemes = new BitThemes();
$modParams = $gBitThemes->getModuleParameters('bitpackage:boards/mod_last_boards_posts.tpl', $gQueryUserId);

?>
