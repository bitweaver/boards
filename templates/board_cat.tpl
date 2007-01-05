{strip}
	<div class="indent">
		{if $child.data.title}
		<h{$heading|default:2}>{$child.data.title|escape}</h{$heading|default:2}>
		{/if}

		{if count($child.members) > 0}
			{include file="bitpackage:bitboards/board_table.tpl" boardsList=$child.members heading=$heading}
		{/if}

		{assign var=heading value=''}

		{if !empty($child.sub_count) && count($child.children)>0}
			{foreach from=$child.children item=schild}
				{include file="bitpackage:bitboards/board_cat.tpl" child=$schild color=$scolor}
			{/foreach}
		{/if}
	</div>
{/strip}
