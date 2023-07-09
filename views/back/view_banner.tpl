{foreach from=$banners.langs item=$banner key=key}
	<div class="banner_admin_container">
		<b>IDIOMA {$banner.iso_code|upper}</b><br>
		<img src="{$url_site}{$banner.background}" class="img-responsive">
	</div>
{/foreach}