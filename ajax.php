<?php
/**
 * AJAX Function Call Stuff
 *
 * reqs:
 *   1 - list all boards
 *   2 - switch lock state on a given thread
 *   3 - switch sticky state on a given thread
 */

require_once( '../bit_setup_inc.php' );

require_once( BITBOARDS_PKG_PATH.'BitBoardTopic.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardPost.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );
require_once( BITBOARDS_PKG_PATH.'BitBoardForum.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'bitboards' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_bitboards_read' );

function ajax_nice_error($errno, $errstr, $errfile, $errline) {
	$errortype = array (
	E_ERROR => array(
	'desc'=>"Error",
	'ignore'=>false),
	E_WARNING => array(
	'desc'=> "Warning",
	'ignore'=>false),
	E_PARSE => array(
	'desc'=> "Parsing Error",
	'ignore'=>false),
	E_NOTICE => array(
	'desc'=> "Notice",
	'ignore'=>true),
	E_CORE_ERROR => array(
	'desc'=> "Core Error",
	'ignore'=>false),
	E_CORE_WARNING => array(
	'desc'=> "Core Warning",
	'ignore'=>false),
	E_COMPILE_ERROR => array(
	'desc'=> "Compile Error",
	'ignore'=>false),
	E_COMPILE_WARNING => array(
	'desc'=> "Compile Warning",
	'ignore'=>false),
	E_USER_ERROR => array(
	'desc'=> "User Error",
	'ignore'=>false),
	E_USER_WARNING => array(
	'desc'=> "User Warning",
	'ignore'=>false),
	E_USER_NOTICE => array(
	'desc'=> "User Notice",
	'ignore'=>false),
	E_STRICT => array(
	'desc'=> "Runtime Notice",
	'ignore'=>true),
	);
	// set of errors for which a var trace will be saved

	if(!$errortype[$errno]['ignore']) {
		$l = ob_get_level();
		if ($l>0) {
			$body = ob_get_contents();
			ob_end_clean();
		}
		static $sent=false;
		if (!$sent) {
			header("HTTP/1.0 500 Internal Server Error");
			echo "<h1>PHP Exception</h1>";
			$sent=true;
		}
		$str= "<br />\n<b>{$errortype[$errno]['desc']}</b>: $errstr in <b>$errfile</b> on line <b>$errline</b>\n<br />\n";
		echo $str;//. "<pre>". htmlspecialchars(var_export($vars,true))."</pre>";
		if ($l>0) {
			ob_start();
			echo $body;
		}
	}
}

set_error_handler("ajax_nice_error");

switch ($_GET['req']) {
	case 1:
		$board = new BitBoardForum();
		$boardList=$board->getForumBoardSelectList();
		$gBitSmarty->assign_by_ref('boardList',$boardList);
		$gBitSmarty->display('bitpackage:bitboards/ajax.tpl');
		break;
	case 2:
		// Now check permissions to access this page
		$gBitSystem->verifyPermission( 'p_board_edit' );
		unset($gContent);
		require_once( BITBOARDS_PKG_PATH.'lookup_inc.php' );
		if($gContent->lock($_REQUEST["locked"]) ) {
			$gContent->load();
			$gBitSmarty->assign_by_ref('flip',$gContent->getFlipFlop());
			$gBitSmarty->assign('flip_name','locked');
			$gBitSmarty->display('bitpackage:bitboards/flipswitch.tpl');
		} else {
			trigger_error(var_export($gContent->mErrors,true ));
			va($gContent);
		}
		break;
	case 3:
		// Now check permissions to access this page
		$gBitSystem->verifyPermission( 'p_board_edit' );

		require_once( BITBOARDS_PKG_PATH.'lookup_inc.php' );
		if($gContent->sticky($_REQUEST["sticky"]) ) {
			$gContent->load();
			$gBitSmarty->assign_by_ref('flip',$gContent->getFlipFlop());
			$gBitSmarty->assign('flip_name','sticky');
			$gBitSmarty->display('bitpackage:bitboards/flipswitch.tpl');
		} else {
			trigger_error(var_export($gContent->mErrors,true ));
		}
		break;
	case 4:

	default:
		break;
}
?>