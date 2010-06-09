{* $Header$ *}
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
		{foreachelse}
			{tr}No message boards found{/tr}
		{/foreach}

		{include file="bitpackage:boards/legend_inc.tpl"  boardicons=1}
	</div><!-- end .body -->
</div><!-- end .admin -->
{/strip}
