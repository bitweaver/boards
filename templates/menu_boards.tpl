{strip}
	<ul>
		{if $gBitUser->hasPermission( 'p_boards_read')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}index.php">{biticon ipackage="icons" iname="go-home" iexplain="Boards Home" iforce="icon"} {tr}Boards Home{/tr}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_boards_edit')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}edit.php">{biticon ipackage="icons" iname="folder-new" iexplain="Create new Board" iforce="icon"} {tr}Create new Board{/tr}</a></li>
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}assign.php">{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="Assign to Board" iforce="icon"}{tr}Assign content to Board{/tr}</a></li>
		{/if}
	</ul>
{/strip}
