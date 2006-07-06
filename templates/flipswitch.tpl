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
{strip}
{if $gBitUser->hasPermission( 'p_boards_edit' )}
<span>
	<a onclick="
	var url = '{$smarty.const.BITBOARDS_PKG_URL}ajax.php?req={$flip.$flip_name.req}&seq='
		+ new Date().getTime()+
		'&{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
		&{$flip_name}={$flip.$flip_name.state|escape:"url"}';
	var element = this.parentNode;
	var params = null;
	{literal}
		var ajax = new Ajax.Updater(
		{success: element},
		url, {method: 'get', parameters: params, onFailure: reportError}
	);
	{/literal}
	return false;
	" href="{$smarty.const.BITBOARDS_PKG_URL}topic.php?
		{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}
	    &{$flip_name}={$flip.$flip_name.state|escape:"url"}">
{/if}
{if $flip.$flip_name.state==1}
	{biticon ipackage=bitboards iname=$flip.$flip_name.up iexplain=$flip.$flip_name.upname}
{else}
	{if $gBitUser->hasPermission( 'p_bitboards_edit' )}
		{biticon ipackage=bitboards iname=$flip.$flip_name.down iexplain=$flip.$flip_name.downname}
	{/if}
{/if}
{if $gBitUser->hasPermission( 'p_bitboards_edit' )}
	</a>
</span>
{/if}
{/strip}