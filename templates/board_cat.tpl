{strip}
	<h2>{$child.data.title}</h2>

	{if count($child.members) > 0}
		<div class="indent">
			{include file="bitpackage:bitboards/board_table.tpl" boardsList=$child.members}
		</div>
	{/if}

	{if !empty($child.sub_count) && count($child.children)>0}
		<div class="indent">
			{foreach from=$child.children item=schild}
				{include file="bitpackage:bitboards/board_cat.tpl" child=$schild color=$scolor}
			{/foreach}
		</div>
	{/if}
{/strip}
