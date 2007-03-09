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
{if $gBitUser->hasPermission( $flip.$flip_name.perm )}
	<span>
		<a onclick="
		var url = '{$smarty.const.BITBOARDS_PKG_URL}ajax.php?req={$flip.$flip_name.req}&amp;seq='
			+ new Date().getTime()+
			'&amp;{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
			&amp;{$flip_name}={$flip.$flip_name.state|escape:"url"}';
		var element = this.parentNode;
		var params = null;
			var ajax = new Ajax.Updater(
			{ldelim}success: element{rdelim},
			url,
			{ldelim}method: 'get', parameters: params, onFailure: reportError{rdelim}
		);
		return false;
		" href="{$smarty.const.BITBOARDS_PKG_URL}topic.php?
			{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
			&amp;{$flip_name}={$flip.$flip_name.state|escape:"url"}">

{if $flip.$flip_name.state==1}
		{biticon ipackage=icons iname=$flip.$flip_name.up iexplain=$flip.$flip_name.upname iforce="icon"}
{else}
		{if $gBitUser->hasPermission( $flip.$flip_name.perm )}
			{biticon ipackage=icons iname=$flip.$flip_name.down iexplain=$flip.$flip_name.downname iforce="icon"}
		{/if}
{/if}

		</a>
	</span>
{/if}
{/strip}
