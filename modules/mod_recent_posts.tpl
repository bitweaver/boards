{* $Header: /cvsroot/bitweaver/_bit_boards/modules/mod_recent_posts.tpl,v 1.5 2007/05/03 08:10:23 bitweaver Exp $ *}
{strip}
{if $gBitSystem->isPackageActive('boards') && {$modLastBoardPosts}
	{bitmodule title="$moduleTitle" name="last_board_posts"}
		<ul class="boards">
			{section name=ix loop=$modLastBoardPosts}
				<li class="{cycle values="odd,even"}">
					<a href="{$modLastBoardPosts[ix].display_url}">{$modLastBoardPosts[ix].title|default:"Comment"}</a>
					<div class="date">
						by {displayname hash=$modLastBoardPosts[ix]} {$modLastBoardPosts[ix].created|bit_short_date}
					</div>
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ul>
		<a href="{$smarty.const.BOARDS_PKG_URL}{if $modRecentPostsBoardId}index.php?b={$modRecentPostsBoardId}{/if}">View More...</a>
	{/bitmodule}
{/if}
{/strip}
