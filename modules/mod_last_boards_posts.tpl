{* $Header: /cvsroot/bitweaver/_bit_boards/modules/Attic/mod_last_boards_posts.tpl,v 1.2 2007/03/08 03:10:00 spiderr Exp $ *}
{strip}
{if $gBitSystem->isPackageActive('bitboards') && {$modLastBoardPosts}
	{bitmodule title="$moduleTitle" name="last_board_posts"}
		<ul class="boards">
			{section name=ix loop=$modLastBoardPosts}
				<li class="{cycle values="odd,even"}">
					<div class="date">{$modLastBoardPosts[ix].created|bit_long_date}
					<br />
					by {displayname hash=$modLastBoardPosts[ix]}</div>
					
					<a href="{$modLastBoardPosts[ix].display_url}">{$modLastBoardPosts[ix].title|default:"Comment"}</a>
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ul>
	{/bitmodule}
{/if}
{/strip}
