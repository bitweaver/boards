{strip}
{*
$flip_state=>$flip.$flip_name.state
$flip_name
$flip_req=>$flip.$flip_name.req
$flip_id=>$flip.$flip_name.id
$flip_idname=>$flip.$flip_name.idname
$flip_up=>$flip.$flip_name.up
$flip_upname=>$flip.$flip_name.upname
$flip_down=>$flip.$flip_name.down
$flip_downname=>$flip.$flip_name.downname
*}
<span id="flip_{$flip.$flip_name.id}-{$flip_name}">
{if $gBitUser->hasPermission( $flip.$flip_name.perm )}
	{if !$gBitThemes->isJavascriptEnabled()}
		<a href="{$smarty.const.BOARDS_PKG_URL}view_board_inc.php?t={$flip.$flip_name.id}&amp;
			{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
			&amp;{$flip_name}={$flip.$flip_name.state|escape:"url"}" />
	{else}	
		<a href="javascript:void(0);" onclick="BitBoards.flipName( '{$smarty.const.BOARDS_PKG_URL}ajax.php?t={$flip.$flip_name.id}&amp;req={$flip.$flip_name.req}&amp;seq='
			+ new Date().getTime()+
			'&amp;{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
			&amp;{$flip_name}={$flip.$flip_name.state|escape:"url"}', '{$flip.$flip_name.id}-{$flip_name}' )" >
	{/if}
{/if}
{if $flip.$flip_name.state==1}
	{biticon ipackage=icons iname=$flip.$flip_name.up iexplain=$flip.$flip_name.upname iforce="icon"}
{else}
	{biticon ipackage=icons iname=$flip.$flip_name.down iexplain=$flip.$flip_name.downname iforce="icon"}
{/if}
{if $gBitUser->hasPermission( $flip.$flip_name.perm )}
	</a>
{/if}
</span>
{/strip}
