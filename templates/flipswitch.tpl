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
{if $gContent->hasUserPermission( $flip.$flip_name.perm )}
<a href="{$smarty.const.BOARDS_PKG_URL}edit_topic.php?{$flip.$flip_name.idname}={$flip.$flip_name.id|escape:"url"}&amp;{$flip_name}={$flip.$flip_name.state|escape:"url"}" />
	{if $flip.$flip_name.state==1}
		{booticon ipackage=icons iname=$flip.$flip_name.up iexplain=$flip.$flip_name.upname}
	{else}
		{booticon ipackage=icons iname=$flip.$flip_name.down iexplain=$flip.$flip_name.downname}
	{/if}
</a>
{elseif $flip.$flip_name.state==1}
	{booticon iname=$flip.$flip_name.up iexplain=$flip.$flip_name.upname}
{/if}
</span>
{/strip}
