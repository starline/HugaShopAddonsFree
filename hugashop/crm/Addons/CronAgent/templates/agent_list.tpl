{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Cron агенты'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <a class="add" href={'AddonCronAgentNew'|link}>Добавить агента</a>
    </div>

    <div id="main_list">
        {if $agents}
            <div class="list">
                {foreach $agents as $agent}
                    <div class="list_row {if !$agent->enabled}enabled_off{/if}">
                        <div class="row col">
                            <div class="col-12 col-sm-8">
                                <a href={'AddonCronAgentAgent'|link:[id => $agent->id]}>{$agent->name}</a>
                            </div>
                            <div class="col-12 col-sm-4 text-end">
                                <span class="badge text-bg-round">
                                    {if $agent->last_run_at}
                                        Последнее срабатывание: {$agent->last_run_at|date} {$agent->last_run_at|time}
                                    {else}
                                        Ещё не запускался
                                    {/if}
                                </span>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        {else}
            Нет агентов
        {/if}
    </div>
{/block}