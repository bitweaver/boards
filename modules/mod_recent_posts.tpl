{* $Header: /cvsroot/bitweaver/_bit_boards/modules/mod_recent_posts.tpl,v 1.2 2007/03/09 22:27:35 spiderr Exp $ *}
{strip}
{if $gBitSystem->isPackageActive('bitboards') && {$modLastBoardPosts}
	{bitmodule title="$moduleTitle" name="last_board_posts"}
		<ul class="boards">
			{section name=ix loop=$modLastBoardPosts}
				<li class="{cycle values="odd,even"}">
					<a href="{$modLastBoardPosts[ix].display_url}">{$modLastBoardPosts[ix].title|default:"Comment"}</a>
					<br />
					<div class="date">by {displayname hash=$modLastBoardPosts[ix]}<br/>
					{$modLastBoardPosts[ix].created|bit_long_date}
</div>
					
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ul>
		<a href="{$smarty.const.BITBOARDS_PKG_URL}{if $modRecentPostsBoardId}index.php?b={$modRecentPostsBoardId}{/if}">View More...</a>
	{/bitmodule}
{/if}
{/strip}
