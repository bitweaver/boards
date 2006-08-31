{if $boardInfo.board_id}
		<a title="{tr}Discussion{/tr}" href="{$smarty.const.BITBOARDS_PKG_URL}?b={$boardInfo.board_id}&amp;filter_id={$gContent->mContentId}">{biticon ipackage="bitboards" iname="discuss_small" iexplain="Discuss"} <strong>({$boardInfo.post_count} {tr}comments{/tr})</strong></a>
{/if}
