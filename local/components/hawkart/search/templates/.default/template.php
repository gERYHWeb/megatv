<form action="#" class="search-form<?/*if($APPLICATION->GetCurDir()=="/personal/records/"):?> is-cabinet-search-form<?endif;*/?>" data-module="search-form">
    <script type="text/x-config">
        {
         "url": "<?= $templateFolder ?>/ajax.php?query=%QUERY",
         "wildcard": "%QUERY"
        }
    </script>
	<div class="form-group has-feedback">
		<label for="" class="sr-only">Название программы или сериала</label>
		<input type="text" data-type="search-field" name="q" id="" class="form-control" placeholder="Название программы или сериала">
		<span class="form-control-feedback"><span data-icon="icon-search"></span></span>
	</div>
</form>