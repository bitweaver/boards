{strip}
<ul class="iconlegend">
	{if $boardicons}
		<li>{biticon ipackage="icons" iname="media-record"         ipath=large iexplain="New Posts"} {tr}New Posts{/tr}</li>
		<li>{biticon ipackage="icons" iname="media-playback-pause" ipath=large iexplain="No New Posts"} {tr}No New Posts{/tr}</li>
	{/if}
	{if $topicicons}
		<li>{biticon ipackage="icons" iname="media-playback-start" ipath=large iexplain="Thread Open"} {tr}Thread Open{/tr}</li>
		<li>{biticon ipackage="icons" iname="emblem-readonly"      ipath=large iexplain="Thread Closed"} {tr}Tread Closed{/tr}</li>
		<li>{biticon ipackage="icons" iname="emblem-important"     ipath=large iexplain="Sticky"} {tr}Sticky{/tr}</li>
		<li>{biticon ipackage="icons" iname="media-eject"          ipath=large iexplain="Not Sticky"} {tr}Not Sticky{/tr}</li>
		<li>{biticon ipackage="icons" iname="media-record"         ipath=large iexplain="New Posts"} {tr}New Posts{/tr}</li>
		<li>{biticon ipackage="icons" iname="media-playback-stop"  ipath=large iexplain="No New Posts"} {tr}No New Posts{/tr}</li>
	{/if}
	{if $posticons}
		<li>{biticon ipackage="icons" iname="mail-reply-sender"    ipath=large iexplain="Post Reply"} {tr}Post Reply{/tr}</li>
	{/if}
</ul>
{/strip}
