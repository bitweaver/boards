<?php
global $gBitSystem;

$registerHash = array(
'package_name' => 'bitboards',
'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'bitboard' ) ) {
	$gBitSystem->registerAppMenu( BITBOARDS_PKG_NAME, ucfirst( BITBOARD_PKG_DIR ), BITBOARD_PKG_URL.'index.php', 'bitpackage:bitboard/menu_bitforum.tpl', BITBOARD_PKG_NAME );
}
if (!function_exists('reltime')) {
	function reltime($time) {
		$m = 60;
		$h = 3600;
		$d = $h * 24;
		$w = $d * 7;
		$M = $w * 4;

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
				return date('l h:i:s A',$time);
			}
		} elseif ($delta<($M)) {
			return date('l dS \a\t h:i:s A',$time);
		} else {
			return date('l dS \of F Y h:i:s A',$time);
		}
	}
}
if (!function_exists('avatar')) {
	function avatar($user_id) {
		$u = new BitUser($user_id);
		$u->load();
		if (!empty($u->mInfo['avatar_url'])) {
			return "<img src=\"{$u->mInfo['avatar_url']}\" class=\"thumb\" title=\"".('Avatar')."\" alt=\"".('Avatar')."\"/>";
		} else {
			return "";
		}
	}
}
?>
