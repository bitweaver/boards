<?php
/**
 * Params: 
 * - title : if is "title", show the title of the post, else show the date of creation
 *
 * @version $Header: /cvsroot/bitweaver/_bit_boards/modules/Attic/mod_last_boards_posts.php,v 1.1 2007/03/08 01:19:12 spiderr Exp $
 * @package blogs
 * @subpackage modules
 */

/**
 * required setup
 */

include_once( BOARDS_PKG_PATH.'BitBoards.php' );
require_once( USERS_PKG_PATH.'BitUser.php' );

global $gBitSmarty, $gQueryUserId, $module_rows, $module_params, $gBitSystem;

$listHash = array( 'user_id' => $gQueryUserId, 'sort_mode' => 'created_desc', 'max_records' => $module_rows, 'parse_data' => TRUE );
$blogPost = new BitBlogPost();
$ranking = $blogPost->getList( $listHash );

$gBitThemes = new BitThemes();
$modParams = $gBitThemes->getModuleParameters('bitpackage:blogs/mod_last_blog_posts.tpl', $gQueryUserId);

$maxPreviewLength = (!empty($modParams['max_preview_length']) ? $modParams['max_preview_length'] : MAX_BLOG_PREVIEW_LENGTH);
$user_blog_id = NULL;
if( count( $ranking['data'] ) ) {
	$user_blog_id = $ranking['data'][0]['blog_id'];
}
$gBitSmarty->assign('user_blog_id', $user_blog_id);

$gBitSmarty->assign('maxPreviewLength', $maxPreviewLength);
$gBitSmarty->assign('modLastBlogPosts', $ranking["data"]);
$gBitSmarty->assign('modLastBlogPostsTitle',(isset($module_params["title"])?$module_params["title"]:""));
$gBitSmarty->assign('blogsPackageActive', $gBitSystem->isPackageActive('blogs'));
?>
