{* $Header: /cvsroot/bitweaver/_bit_boards/modules/Attic/mod_last_boards_posts.tpl,v 1.1 2007/03/08 01:19:12 spiderr Exp $ *}
{strip}
{if $boardsPackageActive}
	{bitmodule title="$moduleTitle" name="last_blog_posts"}
		<ul class="boards">
			{section name=ix loop=$modLastBoardPosts}
				<li class="{cycle values="odd,even"}">
					<div class="date">{$modLastBoardPosts[ix].created|bit_long_date}
					<br />
					by {displayname hash=$modLastBoardPosts[ix]}</div>
					{$modLastBoardPosts[ix].parsed_data|truncate:$maxPreviewLength}
					<br />
					<a href="{$modLastBoardPosts[ix].post_url}">Read more</a>
				</li>
			{sectionelse}
				<li></li>
			{/section}
		</ul>
		{if $user_blog_id}
			<div style="text-align:center;"><a href="{$smarty.const.BIT_ROOT_URL}boards/view.php?blog_id={$user_blog_id}">Visit my blog</a></div>
		{/if}
	{/bitmodule}
{/if}
{/strip}
