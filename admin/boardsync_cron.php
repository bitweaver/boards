<?php
global $gShellScript, $gArgs, $gBitUser;
chdir( dirname( __FILE__ ) );
$gShellScript = TRUE;
//$gDebug = TRUE;

require_once( '../../kernel/includes/setup_inc.php' );

require_once( BOARDS_PKG_INCLUDE_PATH.'admin/boardsync_inc.php');

print "Running: ".date(DATE_RFC822)."\r\n";

board_sync_run(TRUE);
