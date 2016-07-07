<div class="search" data-module="search">
    <script type="text/x-config">
        {
         "url": "<?= $templateFolder ?>/ajax.php?query=%QUERY",
         "wildcard": "%QUERY"
        }
    </script>
    <form action="#" class="search-form<?/*if($APPLICATION->GetCurDir()=="/personal/records/"):?> is-cabinet-search-form<?endif;*/?>">
    	<div class="form-group has-feedback" data-type="search-group">
    		<label for="" class="sr-only">Название программы или сериала</label>
    		<input type="text" data-type="search-field" name="q" id="" class="form-control" placeholder="Название программы или сериала">
    		<span data-icon="icon-search"></span>
    	</div>
        <div class="search-close" data-type="close">
		</div>
    </form>
    <div class="search-trigger" data-type="open">
		<span data-icon="icon-search"></span>
	</div>
</div>			