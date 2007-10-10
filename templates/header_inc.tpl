{if $smarty.const.ACTIVE_PACKAGE == 'boards'}
	<link rel="stylesheet" title="{$style}" type="text/css" href="{$smarty.const.BOARDS_PKG_URL}styles/boards.css" media="all" />
	<script type="text/javascript">/* <![CDATA[ */
		{literal}
		/* DEPENDENCIES: MochiKit Base Async, BitAjax */
		BitBoards = {
			/* this is called from flipswitch.tpl */
			"flipName": function( url, elm ){
				var url = url;
				var element = elm;
				var r = doSimpleXMLHttpRequest(url);
				r.addCallback( BitAjax.updaterCallback, element ); 
				r.addErrback( BitBoards.reportError );
				return false;
			},
			
			/* this is called from post_display.tpl */
			"warn":function( elmid, caller ){
				var oldonclick = caller.onclick;
				document.getElementById( elmid ).style['display']='inline';
				caller.onclick = function(
					document.getElementById( elmid ).style['display']='none';
					caller.onclick = oldonclick;
					return false;
				);
				return false;
			},
			
			/* this is called from list_topic.tpl */
			"moveThread": function( elmid, targetid, url, caller ){
				$( elmid ).style['display']='inline';
				var url = url;
				var element = $(targetid);
				var r = doSimpleXMLHttpRequest(url);
				r.addCallback( BitAjax.updaterCallback, element ); 
				r.addErrback( BitBoards.reportError );
				return false;
								
				var oldonclick=caller.onclick;
				caller.onclick=function(
					document.getElementById(elmid).style['display']='none';
					document.getElementById(targetid).innerHTML='';
					caller.onclick = oldonclick;
					return false;
				);
				return false;
			},
			
			"reportError": function(request) {
				var body = document.getElementsByTagName('body');
				body = body.item(0);
				var div = document.getElementById('ajax_error_div');
				if (div == null) {
					div = document.createElement('div');
					div.setAttribute('id','ajax_error_div');
				}
				div.style['position']='absolute';
				div.style['top']="10%";
				div.style['left']="25%";
				div.style['right']="25%";
				div.style['width']="50%";
				div.style['padding']="10px";
				div.style['backgroundColor']="red";
				div.style['border']="3px solid yellow";
				div.style['zIndex']="100";
				div.innerHTML="<h1 style=\"text-align: center; margin: 10px;\">AJAX Error</h1>"+
					"<h2 style=\"text-align: center; margin: 10px;\">"+request.statusText+"</h2>"+
					request.responseText+
					"<p style=\"text-align:center;\"><small>Click to Close Message</small></p>";
				body.insertBefore(div,body.firstChild);
				div.onclick=function close() {
					this.style['display']='none';
					this.parentNode.removeChild(this);
				}
			}
		}			
		
		{/literal}
	/* ]]> */</script>

	{if $gBitSystem->isPackageActive( 'rss' ) && !empty($board)}
		<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} RSS" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=rss20&amp;b={$smarty.request.b}" />
		<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} ATOM" href="{$smarty.const.BOARDS_PKG_URL}boards_rss.php?version=atom&amp;b={$smarty.request.b}" />
	{/if}
{/if}
