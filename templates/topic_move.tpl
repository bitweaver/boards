{* $Header: /cvsroot/bitweaver/_bit_boards/templates/topic_move.tpl,v 1.4 2006/07/29 15:10:00 hash9 Exp $ *}
{strip}
<div class="floaticon">{bithelp}</div>

<div class="admin bitboard">
	<div class="header">
		<h1>{tr}Move Topic{/tr}: {$gContent->mInfo.title|escape}</h1>
	</div>

	<div class="body">
		{form enctype="multipart/form-data" id="editbitboardform"}
			{assign var=title value=$gContent->mInfo.title|escape}
			<input type="hidden" name="ref" value="-" />
			<input type="hidden" name="thread_id" value="{$smarty.request.thread_id}" />
			<div class="row">
				{formlabel label="To Board" for="target"}
				{forminput}
					<select name="target" id="target">
						{section name=ix loop=$boards}
							<option value="{$boards[ix].content_id|escape}">{$boards[ix].title|escape|truncate:20:"...":true} [{$boards[ix].post_count|escape}]</option>
						{sectionelse}
							<option>{tr}No records found{/tr}</option>
						{/section}
					</select>
				{/forminput}
			</div>
			<div class="row submit">
				<input type="submit" name="move_thread" value="{tr}Move Topic{/tr}" />
			</div>
		{/form}
	</div><!-- end .body -->
</div><!-- end .bitboard -->

{/strip}
