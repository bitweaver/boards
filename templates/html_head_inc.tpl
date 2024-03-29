{if $gBitSystem->getActivePackage() == 'boards'}
	<script>/* <![CDATA[ */
		{literal}
		/* DEPENDENCIES: MochiKit Base Async, BitAjax */
		BitBoards = {
			/* this is called from flipswitch.tpl */
			"flipName": function( url, elm ){
				var url = url;
				var elm = elm;
				BitAjax.updater( elm,url );
			},
			
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
			},
			
			/* this is called from list_topic.tpl */
			"moveThread": function( elmid, targetid, url, caller ){
				document.getElementById( elmid ).style['display']='inline';
				var url = url;
				var elm = document.getElementById(targetid);
				BitAjax.updater( elm,url );
				//this makes no sense but was here, so leave it for now but will prolly kill soon - wjames5
				//return false;
								
				var oldonclick=caller.onclick;
				caller.onclick=function() {
					document.getElementById(elmid).style['display']='none';
					document.getElementById(targetid).innerHTML='';
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
