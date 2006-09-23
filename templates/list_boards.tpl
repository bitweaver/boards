{* $Header: /cvsroot/bitweaver/_bit_boards/templates/list_boards.tpl,v 1.1 2006/09/23 03:47:27 spiderr Exp $ *}
{strip}
<div class="listing bitboard">
	<div class="header">
		<h1>{tr}Message Boards{/tr}</h1>
	</div>

	<div class="body">
		{foreach from=$ns item=child}
			{assign var=heading value=1}
			{include file="bitpackage:bitboards/board_cat.tpl" child=$child color=$color}
		{/foreach}

		{include file="bitpackage:bitboards/legend_inc.tpl"  boardicons=1}
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
