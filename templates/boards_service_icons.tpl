{if $boardInfo.board_id}
	<a title="{tr}Discussion{/tr}" href="{$smarty.const.BOARDS_PKG_URL}?b={$boardInfo.board_id}&amp;filter_id={$gContent->mContentId}">{biticon ipackage="boards" iname="discuss_small" iexplain="Discuss"} [<strong>{$boardInfo.post_count}</strong>]</a>
{/if}
