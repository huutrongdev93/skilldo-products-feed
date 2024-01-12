<form id="js_productsFeed_form" method="post">
    <?php echo Admin::loading();?>
	<div class="ui-layout">
	    <div class="ui-title-bar__group">
		    <div class="ui-title-bar__title">Thêm chương trình</div>
		    <div class="ui-title-bar__des mb-2" style="color: #8c8c8c">
			    Đây là link api danh sách sản phẩm dạng XML của website
		    </div>
		    <p><a href="<?php echo Url::admin('system/ProductsFeed');?>">Chọn danh mục mặc định</a></p>
	    </div>
	    <div class="box p-3">
		    <div class="row">
                <?php
                echo FormBuilder::render([
                    'field' 	=> 'name',
                    'label'     => 'Tên Feed',
                    'type'  	=> 'text',
                    'start'     => 12
                ]);
                echo FormBuilder::render([
                    'field' 	=> 'categoryGoogle',
                    'label'     => 'Danh mục Google',
                    'note'      => 'Danh mục được áp dụng đối với những sản phẩm chưa có danh mục google',
                    'type'  	=> 'select',
                    'options'   => $googleCategories,
                    'start'     => 6
                ]);
                echo FormBuilder::render([
                    'field' 	=> 'categoryFacebook',
                    'label'     => 'Danh mục Facebook',
                    'note'      => 'Danh mục được áp dụng đối với những sản phẩm chưa có danh mục facebook',
                    'type'  	=> 'select',
                    'options'   => $facebookCategories,
                    'start'     => 6
                ]);
                ?>
		    </div>
	    </div>
	    <div class="box p-3 js_productsFeed_type_box">
		    <p class="heading">Sản phẩm áp dụng</p>
		    <div class="form-group">
			    <label class="radio d-block">
				    <input type="radio" name="productType" value="category" class="js_productsFeed_input_type" checked> Danh mục sản phẩm
			    </label>
			    <div class="productsFeed_products_box productsFeed_categories">
				    <?php
	                    echo FormBuilder::render([
	                        'field' 	=> 'categoryWebsite',
	                        'label'     => 'Danh mục sản phẩm',
	                        'type'  	=> 'select',
	                        'options'   => $productsCategories,
	                        'start'     => 6
	                    ]);
				    ?>
			    </div>
			    <label class="radio d-block">
				    <input type="radio" name="productType" value="productsCustom" class="js_productsFeed_input_type"> Sản phẩm tùy chọn
			    </label>
			    <label class="radio d-block">
				    <input type="radio" name="productType" value="products" class="js_productsFeed_input_type"> Tất cả sản phẩm
			    </label>
		    </div>
		    <div class="productsFeed_products_box productsFeed_products" style="display: none;">
			    <div class="discount_products_heading mb-1">
				    <label>Sản phẩm được áp dụng</label>
				    <button class="btn btn-blue" type="button" id="js_productsFeed_products_btn_add">Thêm sản phẩm</button>
			    </div>
			    <div class="discount_products_table">
				    <table class="display table table-striped media-table ">
					    <thead>
						    <tr>
							    <th id="image" class="manage-column column-image">Hình</th>
							    <th id="title" class="manage-column column-title">Tiêu Đề</th>
							    <th id="prices" class="manage-column column-prices">Giá</th>
							    <th id="prices" class="manage-column column-prices">Hành động</th>
						    </tr>
					    </thead>
					    <tbody id="js_productsFeed_products_result"></tbody>
				    </table>
			    </div>
		    </div>
	    </div>
    </div>
</form>


<div class="modal fade" id="js_productsFeed_products_modal" aria-hidden="true" aria-labelledby="js_productsFeed_products_modal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5">Chọn Sản Phẩm</h1>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="js_productsFeed_product_search">
					<div class="d-flex gap-2 mb-2 justify-content-end">
						<div class="column">
							<select name="search_category" class="form-control">
                                <?php foreach ($productsCategories as $categoryKey => $categoryName) { ?>
									<option value="<?php echo $categoryKey;?>"><?php echo $categoryName;?></option>
                                <?php } ?>
							</select>
						</div>
						<div class="column">
							<input name="search_name" class="form-control" placeholder="Tên sản phẩm"/>
						</div>
						<div class="column">
							<button id="js_productsFeed_product_btn_search" class="btn btn-red" type="button"><i class="fa-thin fa-magnifying-glass"></i> Tìm</button>
						</div>
					</div>
					<div class="" style="overflow: auto; max-height: 500px">
						<table class="display table table-striped media-table ">
							<thead>
							<tr>
								<th id="cb" class="manage-column column-cb check-column">
									<input type="checkbox" name="select[]" id="select_all" class="icheck">
								</th>
								<th id="image" class="manage-column column-image">Hình</th>
								<th id="title" class="manage-column column-title">Tiêu Đề</th>
								<th id="prices" class="manage-column column-prices">Giá</th>
							</tr>
							</thead>
							<tbody id="js_productsFeed_products_search_result"></tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-blue" id="js_productsFeed_product_btn_confirm" type="button">Xác nhận</button>
			</div>
		</div>
	</div>
</div>

<script id="product_template" type="text/x-custom-template">
	<tr class="js_column js_product_item tr_${id}">
		<td class="image column-image">
			<img src="${image}" loading="lazy">
		</td>
		<td class="title column-title">
	        <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${id}" style="color:#000;">
	            ${title}
	        </span>
		</td>
		<td class="prices column-prices">
			<div class="product-variations-block">
				<div class="product-variations-model ">
					<p class="quick-edit-box d-flex gap-3">
						<span class="product_price">${price}</span>
						<span class="product_price_sale">${price_sale}</span>
					</p>
				</div>
			</div>
		</td>
		<td class="action column-action">
			<button class="btn btn-red js_product_btn_delete" data-id="${id}"><?php echo Admin::icon('delete');?></button>
		</td>
	</tr>
</script>


<script type="text/javascript">
    $(function() {
	    const productsFeed = new ProductsFeedHandle();
	    $(document)
		    .on('change', '.js_productsFeed_input_type', function() {productsFeed.changeProductType($(this))})
		    .on('click', '#js_productsFeed_products_btn_add', function() {productsFeed.showProductModal($(this))})
		    .on('click', '#js_productsFeed_product_btn_search', function() {productsFeed.productSearch($(this))})
		    .on('click', '#js_productsFeed_product_btn_confirm', function() {productsFeed.productAdd($(this))})
		    .on('click', '.js_product_btn_delete', function() {productsFeed.productDelete($(this))})
		    .on('submit', '#js_productsFeed_form', function() { return productsFeed.add($(this))})
    })
</script>