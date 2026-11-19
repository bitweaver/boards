{if $gBitSystem->getActivePackage() == 'boards'}
	<script>/* <![CDATA[ */
		{literal}
		BitBoards = {
			/* this is called from post_display.tpl */
			"warn":function( elmid, caller ){
				var oldonclick = caller.onclick;
				document.getElementById( elmid ).style['display']='inline';
				caller.onclick = function() {
					document.getElementById( elmid ).style['display']='none';
					caller.onclick = oldonclick;
					return false;
				}
				return false;
			}
		}
		
		{/literal}
	/* ]]> */</script>

	{if $gBitSystem->isPackageActive( 'rss' )}
		{if $board && $smarty.request.b}
			<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} RSS" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=rss20&amp;b={$smarty.request.b}{if $gBitSystem->getConfig( 'rssfeed_httpauth' ) && $gBitUser->isRegistered()}&httpauth=y{/if}" />
			<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} ATOM" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=atom&amp;b={$smarty.request.b}{if $gBitSystem->getConfig( 'rssfeed_httpauth' ) && $gBitUser->isRegistered()}&httpauth=y{/if}" />
		{elseif $thread && $smarty.request.t}
			<link rel="alternate" type="application/rss+xml" title="Topic {$thread->getTitle()|escape} RSS" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=rss20&amp;t={$smarty.request.t}{if $gBitSystem->getConfig( 'rssfeed_httpauth' ) && $gBitUser->isRegistered()}&httpauth=y{/if}" />
			<link rel="alternate" type="application/rss+xml" title="Topic {$thread->getTitle()|escape} ATOM" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=atom&amp;t={$smarty.request.t}{if $gBitSystem->getConfig( 'rssfeed_httpauth' ) && $gBitUser->isRegistered()}&httpauth=y{/if}" />
		{/if}
	{/if}
{/if}
