{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/board.tpl,v 1.10 2006/09/01 12:32:23 squareing Exp $ *}
{strip}
<div class="listing bitboard">
	<div class="header">
		<h1>Boards</h1>
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
