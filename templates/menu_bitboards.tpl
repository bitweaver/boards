{strip}
	<ul>
		{if $gBitUser->hasPermission( 'p_bitboard_read')}
			<li><a class="item" href="{$smarty.const.BITBOARDS_PKG_URL}index.php">{tr}BitBoard Home{/tr}</a></li>
		{/if}
	</ul>
{/strip}
