{* $Header: /cvsroot/bitweaver/_bit_boards/templates/list_boards.tpl,v 1.4 2007/05/03 08:10:23 bitweaver Exp $ *}
{strip}
<div class="listing boards">
	<div class="header">
		<h1>{tr}Message Boards{/tr}</h1>
	</div>

	<div class="body">
		{foreach from=$ns item=child}
			{assign var=heading value=1}
			{if $child.sub_count > 0}
				{include file="bitpackage:boards/board_cat.tpl" child=$child color=$color}
			{/if}
		{/foreach}

		{include file="bitpackage:boards/legend_inc.tpl"  boardicons=1}
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
