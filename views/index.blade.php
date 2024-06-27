<script type="text/javascript">
	$(function() {
		const productFeeds = new ProductsFeedTableHandle();
		$(document)
			.on('click', '.js_productsFeed_btn_created_xml', function() { productFeeds.update($(this))})
	})
</script>