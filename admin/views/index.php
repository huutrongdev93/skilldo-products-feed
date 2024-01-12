<div class="ui-layout">
	<div class="ui-title-bar__group">
		<div class="ui-title-bar__title">
			<p class="heading">Products Feed</p>
		</div>
		<div class="ui-title-bar__des mb-2" style="color: #8c8c8c">Đây là link api danh sách sản phẩm dạng XML của website</div>
	</div>
	<div class="box">
		<div class="box-content" style="padding: 10px;">
			<div class="table-responsive">
				<table class="display table table-striped media-table ">
					<thead>
					<tr>
						<th id="title" class="manage-column column-title">Tên chương trình</th>
						<th id="title" class="manage-column column-title">Link XML</th>
						<th id="title" class="manage-column column-type">Loại</th>
						<th id="created" class="manage-column column-created">Ngày Tạo</th>
						<th id="created" class="manage-column column-created">Ngày Cập nhật</th>
						<th id="action" class="manage-column column-action">Hành Động</th>
					</tr>
					</thead>
					<tbody>
                    <?php foreach ($productsFeed as $feed) {?>
						<tr class="tr_<?php echo $feed->id;?>">
							<td class="created column-created"><?php echo $feed->name;?></td>
							<td class="title column-title">
								<h3><a href="<?php echo Url::base('products-feed.xml?sign='.$feed->key);?>" target="_blank"><?php echo Url::base('products-feed.xml?sign='.$feed->key);?></a></h3>
							</td>
							<td class="created column-type">
								<?php if($feed->type == 'category') echo 'Danh mục';?>
								<?php if($feed->type == 'products') echo 'Tất cả sản phẩm';?>
								<?php if($feed->type == 'productsCustom') echo 'Sản phẩm tùy chọn';?>
							</td>
							<td class="created column-created"><?php echo $feed->created;?></td>
							<td class="created column-timeUp">
								<span><?php echo (!empty($feed->timeUp)) ? date('d-m-Y H:i', $feed->timeUp) : 'chưa cập nhật';?></span>
                                <?php if (Auth::hasCap('productsFeedEdit')) {?>
								<button class="btn pt-1 pb-1 js_productsFeed_btn_created_xml" data-id="<?php echo $feed->id;?>"><i class="fa-light fa-arrows-rotate"></i></button>
                                <?php } ?>
							</td>
							<td class="action column-action text-center">
								<?php if (Auth::hasCap('productsFeedEdit')) {?><a href="<?php echo Url::admin('plugins?page=products-feed&view=edit&key='.$feed->key);?>" class="btn-blue btn"><?php echo Admin::icon('edit');?></a><?php } ?>
                                <?php if (Auth::hasCap('productsFeedDelete')) { echo Admin::btnDelete(['trash' => 'disable', 'id' => $feed->id, 'module' => 'PrFeed', 'des' => 'Bạn chắc chắn muốn xóa '.$feed->name.' ?']); } ?>
							</td>
						</tr>
                    <?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(function() {
		const productFeeds = new ProductsFeedTableHandle();
		$(document)
			.on('click', '.js_productsFeed_btn_created_xml', function() {productFeeds.update($(this))})
	})
</script>