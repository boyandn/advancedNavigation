
<section id="advancedNavigation" class="panel widget">
	<div class="panel-heading">
		<i class="icon-time"></i> {l s='Advanced Navigation'}
	</div>
	<section id="advancedNavigation_notifications" class="loading">
		<div class="alert alert-{if $categories > 0}danger{else}success{/if}" role="alert">
			Duplicated categories urls: {$categories}
		</div>
		<div class="alert alert-{if $products > 0}danger{else}success{/if}" role="alert">
			Duplicated products urls: {$products}
		</div>
	</section>

</section>

