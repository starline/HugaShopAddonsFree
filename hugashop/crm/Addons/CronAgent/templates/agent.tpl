{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{if $agent->id}
    {$meta_title = $agent->name}
{else}
    {$meta_title = 'Новый агент'}
{/if}

{block name=content}
    <form method="post">
        <input name="id" type="hidden" value="{$agent->id}" />
        {getCSRFInput}

        <div class="row gx-4">
            <div class="col-12">
                <div class="checkbox_line">
                    <div class="form-check form-switch">
                        <input type="hidden" name="enabled" value="0">
                        <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch" id="enabled"
                            {if $agent->enabled}checked{/if} />
                        <label class="form-check-label" for="enabled">Активен</label>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label" for="name">Название</label>
                    <input class="form-control form-control-lg" id="name" name="name" type="text" value="{$agent->name}"
                        autocomplete="off" />
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label" for="description">Описание</label>
                    <textarea class="form-control" id="description" name="description">{$agent->description}</textarea>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="mb-3">
                    <label class="form-label" for="start_at">Дата запуска</label>
                    <input class="form-control" id="start_at" name="start_at" type="datetime-local"
                        value="{$agent->start_at|date_format:'Y-m-d\\TH:i'}" />
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="period_hours">Период (часы)</label>
                    <input class="form-control" id="period_hours" name="period_hours" type="number"
                        value="{$agent->period_hours}" min="0" />
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="mb-3">
                    <label class="form-label" for="period_minutes">Период (минуты)</label>
                    <input class="form-control" id="period_minutes" name="period_minutes" type="number"
                        value="{$agent->period_minutes}" min="0" max="59" />
                </div>
            </div>

            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label" for="function">Функция</label>
                    <input class="form-control" id="function" name="function" type="text" value="{$agent->function}"
                        autocomplete="off" />
                </div>
            </div>

            <div class="col-12 btn_row">
                {include file="parts/button.tpl" label="Сохранить"}
            </div>
        </div>
    </form>
{/block}