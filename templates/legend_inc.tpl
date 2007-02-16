{strip}
<ul class="iconlegend">
	{if $boardicons}
		<li>{biticon ipackage="icons" iname="folder-new"         ipath=large iexplain="New Posts" iforce="icon"} {tr}New Posts{/tr}</li>
		<li>{biticon ipackage="icons" iname="folder" ipath=large iexplain="No New Posts" iforce="icon"} {tr}No New Posts{/tr}</li>
	{/if}
	{if $topicicons}
		<li>{biticon ipackage="icons" iname="emblem-readonly"      ipath=large iexplain="Thread Closed" iforce="icon"} {tr}Thread Closed{/tr}</li>
		<li>{biticon ipackage="icons" iname="emblem-important"     ipath=large iexplain="Sticky" iforce="icon"} {tr}Sticky{/tr}</li>
		<li>{biticon ipackage="icons" iname="folder-new"            ipath=large iexplain="New Posts" iforce="icon"} {tr}New Posts{/tr}</li>
	{/if}
	{if $posticons}
		<li>{biticon ipackage="icons" iname="mail-reply-sender"    ipath=large iexplain="Post Reply" iforce="icon"} {tr}Post Reply{/tr}</li>
	{/if}
</ul>
{/strip}
