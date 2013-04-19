{if $boardInfo.board_id}
	<a title="{tr}Discussion{/tr}" href="{$smarty.const.BOARDS_PKG_URL}?b={$boardInfo.board_id}&amp;filter_id={$gContent->mContentId}">{booticon ipackage="boards" iname="icon-comments" iexplain="Discuss"} [<strong>{$boardInfo.post_count}</strong>]</a>
{/if}
