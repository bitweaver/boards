{strip}
	<ul>
		{if $gBitUser->hasPermission( 'p_boards_read')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}index.php">{booticon iname="icon-home" ipackage="icons" iexplain="Boards Home" ilocation=menu}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_boards_create')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}edit.php">{booticon iname="icon-folder-close"   ipackage="icons" iexplain="Create new Board" ilocation=menu}</a></li>
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}assign.php">{booticon iname="icon-circle-arrow-right"   ipackage="icons" iexplain="Assign to Boards" ilocation=menu}</a></li>
		{/if}
	</ul>
{/strip}
