{include file='navbar.tpl'}

<div class="container">
    <div class="card">
        <div class="card-block">
            {if isset($CONFIGURE)}
				<div class="alert alert-info">{$CONFIGURE}</div>
			{else if isset($ERROR)}
				<div class="alert alert-danger">{$ERROR}</div>
			{else}
				<div class="row">
					<div class="col-md-7">
						{* Top voters *}
						<h3>{$TOP_VOTERS}</h3>
						{if count($RESULTS)}
						  <table class="table table-responsive dataTables-topList">
							<colgroup>
							   <col span="1" style="width: 40%;">
							   <col span="1" style="width: 15%;">
							   <col span="1" style="width: 15%">
							   <col span="1" style="width: 15%">
							   <col span="1" style="width: 15%">
							</colgroup>
							<thead>
							  <tr>
								<th>{$USERNAME}</th>
								<th>{$DAILY_VOTES}</th>
								<th>{$WEEKLY_VOTES}</th>
								<th>{$MONTHLY_VOTES}</th>
								<th>{$ALL_TIME_VOTES}</th>
							  </tr>
							</thead>
							<tbody>
								{foreach from=$RESULTS item=result}
								<tr>
								  <td>{if $result.exists eq true}<img src="{$result.avatar}" style="max-height:25px;max-width:25px;" class="rounded-circle" alt="{$result.name}" /> <a href="{$result.profile}" style="{$result.user_style}">{$result.nickname}</a>{else}<img src="{$result.avatar}" style="max-height:25px;max-width:25px;" class="rounded-circle" alt="{$result.name}" /> {$result.name}{/if}</td>
								  <td>{$result.daily}</td>
								  <td>{$result.weekly}</td>
								  <td>{$result.monthly}</td>
								  <td>{$result.alltime}</td>
								</tr>
								{/foreach}
							</tbody>
						  </table>
						{/if}
					
					</div>
					
					<div class="col-md-5">
						{* Display sites *}
						<h3>{$VOTE_SITES}</h3>
						{foreach from=$VOTE_SITES_LIST item=site}
							<a href="{$site->site|escape:'htmlall'}" target="_blank" rel="noopener nofollow" class="btn btn-primary btn-block">{$site->name|escape:'htmlall'}</a>
						{/foreach}
					</div>
				</div>
			{/if}
        </div>
    </div>
</div>

{include file='footer.tpl'}