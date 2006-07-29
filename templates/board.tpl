{* $Header: /cvsroot/bitweaver/_bit_boards/templates/Attic/board.tpl,v 1.7 2006/07/29 15:10:00 hash9 Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="listing bitboard">
	<div class="header">
	<h1>Boards</h1>
	</div>

	<div class="body">
	<div class="mb-cat-list">
		{foreach from=$ns item=child}
			{include file="bitpackage:bitboards/board_cat.tpl" child=$child color=$color}
		{/foreach}
	</div>
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
