{strip}
	<ul>
		{if $gBitUser->hasPermission( 'p_boards_read')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}index.php">{biticon ipackage="icons" iname="go-home" iexplain="Boards Home" ilocation=menu}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_boards_create')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}edit.php">{biticon ipackage="icons" iname="folder-new" iexplain="Create new Board" ilocation=menu}</a></li>
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}assign.php">{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="Assign to Boards" ilocation=menu}</a></li>
		{/if}
	</ul>
{/strip}
