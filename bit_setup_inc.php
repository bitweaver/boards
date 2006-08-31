<?php
global $gBitSystem;

$registerHash = array(
'package_name' => 'bitboards',
'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'bitboards' ) ) {
	$gBitSystem->registerAppMenu( BITBOARDS_PKG_NAME, ucfirst( BITBOARDS_PKG_DIR ), BITBOARDS_PKG_URL.'index.php', 'bitpackage:bitboards/menu_bitboards.tpl', BITBOARDS_PKG_NAME );

	require_once( BITBOARDS_PKG_PATH.'BitBoard.php' );

	$gLibertySystem->registerService( LIBERTY_SERVICE_FORUMS, BITBOARDS_PKG_NAME, array(
		'content_display_function' => 'bitboards_content_display',
		'content_preview_function' => 'bitboards_content_preview',
		'content_edit_function' => 'bitboards_content_edit',
		'content_store_function' => 'bitboards_content_store',
		'content_expunge_function' => 'bitboards_content_expunge',
		'content_edit_mini_tpl' => 'bitpackage:bitboards/bitboards_edit_mini_inc.tpl',
//		'content_view_tpl' => 'bitpackage:bitboards/service_view_boards.tpl',
		'content_icon_tpl' => 'bitpackage:bitboards/bitboards_service_icons.tpl',
		'content_list_sql_function' => 'bitboards_content_list_sql',
	) );

}

if (!function_exists('reltime')) {
	function reltime($time,$mode='long') {
		$m = 60;
		$h = 3600;
		$d = $h * 24;
		$w = $d * 7;
		$M = $w * 4;
		$L = $M * 2;

		if (! is_numeric($time)) return $time;
		$delta = (time() - $time);
		if ($delta < 0) {
			$delta = -$delta;
			return tra("In the future!");
		}

		if ($delta<1) {
			return tra("within the last second");
		} elseif ($delta<$m) {
			return round($delta)." seconds ago";
			//return tra("within the last minute");
		} elseif ($delta<$h) {
			if ($delta<$m*2) {
				return "one minute ago";
			} else {
				return round($delta/$m)." minutes ago";
			}
		} elseif ($delta<($d)) {
			if ($delta<$h*1.1) {
				return "one hour ago";
			} elseif ($delta<$d) {
				$delta_hours = floor(($delta-(floor($delta/($h))*($h)))/$m);
				return round($delta/($h))." hours " . $delta_hours . " minutes ago";
			} else {
				return round($delta/$h)." hours ago";
			}
		} elseif ($delta<($w)) {
			if ($delta<($d*1.7)) {
				return "Yesterday " .date('h:i:s A',$time);
			} else {
				if ($mode='short') {
					return date('D H:i:s',$time);
				}
				return date('l h:i:s A',$time);
			}
		} elseif ($delta<($M)) {
			if ($mode='short') {
				return date('D d, H:i',$time);
			}
			return date('l dS \a\t h:i A',$time);
		} elseif ($delta<($L)) {
			if ($mode='short') {
				return date('M d, H:i',$time);
			}
			return date('l dS \of F h:i A',$time);
		} else {
			if ($mode='short') {
				return date('M d, Y H:i',$time);
			}
			return date('l dS \of F Y h:i A',$time);
		}
	}
}
?>
