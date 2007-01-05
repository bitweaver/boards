{if $smarty.const.ACTIVE_PACKAGE == 'bitboards'}
	<link rel="stylesheet" title="{$style}" type="text/css" href="{$smarty.const.BITBOARDS_PKG_URL}styles/bitboards.css" media="all" />
	<script type="text/javascript">/* <![CDATA[ */
		{literal}
		function reportError(request) {
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
		{/literal}
	/* ]]> */</script>

	{if $gBitSystem->isPackageActive( 'rss' ) && !empty($board)}
		<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} RSS" href="{$smarty.const.BITBOARDS_PKG_URL}bitboards_rss.php?version=rss20&amp;b={$smarty.request.b}" />
		<link rel="alternate" type="application/rss+xml" title="Board {$board->mInfo.title|escape} ATOM" href="{$smarty.const.BITBOARDS_PKG_URL}bitboards_rss.php?version=atom&amp;b={$smarty.request.b}" />
	{/if}
{/if}
