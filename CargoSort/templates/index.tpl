{extends file='wrapper/main.tpl'}
{include file='addon/parts/menu_part.tpl'}

{$meta_title='Заполнение контейнеров'}

{block name=content}
	{if $message_error}
		<div class="message message_error">
			<span class="text">
				{if $message_error == 'no_permission'}Установите права на запись в папку {$config->import_files_dir}
				{elseif $message_error == 'convert_error'}Не получилось сконвертировать файл в кодировку UTF8
				{elseif $message_error == 'locale_error'}На сервере не установлена локаль {$locale}, импорт может работать
					некорректно
				{else}{$message_error}
				{/if}
			</span>
		</div>
	{/if}


	{if $filename}
		<h1 class="mb-2">Импорт {$filename|escape}</h1>

		<div class="row gx-5">
			<div class="col-lg-6">
				<ul class="property_block">
					<li>
						<div class="col-form-label">Размер контейнера:</div>
						<div class="col-form-label">{$box_size}</div>
					</li>
					<li>
						<div class="col-form-label">Стоимость груза в контейнере:</div>
						<div class="col-form-label">{$box_cost}</div>
					</li>
					<li>
						<div class="col-form-label">Цена доставки контейнера:</div>
						<div class="col-form-label">{$box_delivery_cost}</div>
					</li>
				</ul>
			</div>
			<div class="col-lg-6">
				<ul class="property_block">
					<li>
						<div class="col-form-label">Общий вес:</div>
						<div class="col-form-label">{$total_size}</div>
					</li>
					<li>
						<div class="col-form-label">Общая стоимость:</div>
						<div class="col-form-label">{$total_cost}</div>
					</li>
					<li>
						<div class="col-form-label">Идеальное кол-во контейнеров по весу:</div>
						<div class="col-form-label">{$ideal_box_count_of_size}</div>
					</li>
					<li>
						<div class="col-form-label">Идеальное кол-во контейнеров по стоимости груза:</div>
						<div class="col-form-label">{$ideal_box_count_of_cost}</div>
					</li>
					<li>
						<div class="col-form-label">Фактическое кол-во контейнеров:</div>
						<div class="col-form-label">{$box_count}</div>
					</li>
				</ul>
			</div>
		</div>
		<p class="mb-2">
			<a class="btn btn-primary" href="/files/exports/cargosort.csv">Скачать в формате CSV</a>
		</p>

		<div class="table-responsive">
			<table class="table table-bordered cargosort-result">
				<thead>
					<tr>
						<th>#</th>
						<th>Размер</th>
						<th>Название</th>
						<th>Вес</th>
						<th>Цена</th>
						<th>Цена доставки</th>
						<th>Ср. Ц. д.</th>
						<th>Очередь</th>
					</tr>
				</thead>
				<tbody>
					{foreach $products as $prod}
						<tr>
							<td>#{$prod.number_of_box}</td>
							<td>{$prod.tyre_size}</td>
							<td>{$prod.name}</td>
							<td>{$prod.size}</td>
							<td>{$prod.cost}</td>
							<td>{$prod.delivery_product_in_box_cost}</td>
							<td>{$prod.middle_delivery_cost}</td>
							<td>{$prod.firstly}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	{else}
		<h1 class="mb-2">Заполнение контейнеров</h1>
		<form method="post" id="product" enctype="multipart/form-data">
			{getCSRFInput}

			<div class="row gx-5">
				<div class="col-lg-6 layer">
					<input class="form-control import_file" name="file" type="file" value="">
					<p class="mt-2">максимальный размер файла &mdash; {$config->max_upload_filesize|byte_convert}</p>
					<div class="alert alert-info">Хавает CSV, стоимость контейнера не должна быть ниже минимальной цены</div>
				</div>

				<div class="col-lg-6 layer">
					<ul class="property_block">
						<li>
							<label class="col-form-label" for="box_size">Размер контейнера:</label>
							<input class="form-control" id="box_size" name="box_size" type="text" value="">
						</li>
						<li>
							<label class="col-form-label" for="box_cost">Стоимость груза в контейнере:</label>
							<input class="form-control" id="box_cost" name="box_cost" type="text" value="">
						</li>
						<li>
							<label class="col-form-label" for="box_delivery_cost">Стоимость доставки контейнера:</label>
							<input class="form-control" id="box_delivery_cost" name="box_delivery_cost" type="text" value="">
						</li>
					</ul>
					<div class="col-12 btn_row">
						{include file="parts/button.tpl" label="Загрузить"}
					</div>
				</div>
			</div>
		</form>

		<div class="block_help">
			<p>В первой строке таблицы должны быть указаны названия колонок в таком формате:</p>
			<ul>
				<li><span>Размер</span> размерность </li>
				<li><span>Модель</span> название модели</li>
				<li><span>Вес</span> вес товара</li>
				<li><span>Цена</span> цена товара</li>
				<li><span>Колличество</span> колличество товара</li>
				<li><span>Колличество в первую очередь</span> колличество товара</li>
			</ul>
		</div>
	{/if}
{/block}

{block name=body_script append}
	<script type="module">
		import '{"js/piecon/piecon.js"|asset}';
	</script>
{/block}