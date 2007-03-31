{strip}
	<ul>
		{if $gBitUser->hasPermission( 'p_boards_read')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}index.php">{tr}Boards Home{/tr}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_boards_edit')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}edit.php">{tr}Create new Board{/tr}</a></li>
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}assign.php">{tr}Assign content to Board{/tr}</a></li>
		{/if}
	</ul>
{/strip}
