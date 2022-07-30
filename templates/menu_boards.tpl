{strip}
{if $packageMenuTitle}<a class="dropdown-toggle" data-toggle="dropdown" href="#"> {tr}{$packageMenuTitle}{/tr} <b class="caret"></b></a>{/if}
<ul class="{$packageMenuClass}">
		{if $gBitUser->hasPermission( 'p_boards_read')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}index.php">{booticon iname="fa-house" iexplain="Browse `$smarty.const.BOARDS_PKG_DIR`"}</a></li>
		{/if}
		{if $gBitUser->hasPermission( 'p_boards_create')}
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}edit.php">{booticon iname="fa-folder-closed" iexplain="Create new `$smarty.const.BOARDS_PKG_DIR`"}</a></li>
			<li><a class="item" href="{$smarty.const.BOARDS_PKG_URL}assign.php">{booticon iname="fa-circle-arrow-right" iexplain="Assign to `$smarty.const.BOARDS_PKG_DIR`"}</a></li>
		{/if}
	</ul>
{/strip}
