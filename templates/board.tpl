{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/board.tpl,v 1.9 2006/08/31 21:52:23 squareing Exp $ *}
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
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
