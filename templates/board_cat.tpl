{strip}
	<div class="indent">
		{if $child.data.title}
			<a href="javascript:toggle('bcat{$child.data.content_id|escape}');"><h{$heading|default:2}>{$child.data.title|escape}</h{$heading|default:2}></a>
		{elseif $gBitSystem->isPackageActive('pigeonholes')}
			<a href="javascript:toggle('bcatnone');"><h{$heading|default:2}>Uncategoried</h{$heading|default:2}></a>
		{/if}
		<div id="bcat{$child.data.content_id|default:none}">
			{if count($child.members) > 0}
				{include file="bitpackage:bitboards/board_table.tpl" boardsList=$child.members heading=$heading}
			{/if}

			{if !empty($child.sub_count) && count($child.children)>0}
				{foreach from=$child.children item=schild}
					{include file="bitpackage:bitboards/board_cat.tpl" child=$schild color=$color}
				{/foreach}
			{/if}
		</div>
	</div>
{/strip}
