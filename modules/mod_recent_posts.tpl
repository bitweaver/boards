{strip}
{if $gBitSystem->isPackageActive('boards') && $modLastBoardPosts}
	{bitmodule title=$moduleTitle name="last_board_posts"}
		<ul class="boards">
			{section name=ix loop=$modLastBoardPosts}
				<li class="{cycle values="odd,even"}">
					<a title="{tr}Author:{/tr} {displayname nolink=1 hash=$modLastBoardPosts[ix]}" href="{$modLastBoardPosts[ix].display_url}">{$modLastBoardPosts[ix].title|default:"Comment"|escape:html}</a>
					<div class="date">
						{displayname hash=$modLastBoardPosts[ix]}, {$modLastBoardPosts[ix].created|bit_short_date}
					</div>
				</li>
			{/section}
			<li class="more"><a class="more" href="{$smarty.const.BOARDS_PKG_URL}{if $modRecentPostsBoardId}index.php?b={$modRecentPostsBoardId}{/if}">{tr}Show more{/tr} &hellip;</a></li>
		</ul>
	{/bitmodule}
{/if}
{/strip}
