class ProductsFeedTableHandle {
	update(element) {

		element.attr('disabled', true);

		element.html('<i class="fa-light fa-light fa-arrows-rotate fa-spin"></i>');

		let data = {
			action: 'AdminProductFeedsAjax::createdXML',
			id: element.attr('data-id')
		}

		request.post(ajax, data).then(function(response) {
			SkilldoMessage.response(response)
			element.removeAttr('disabled');
			element.html('<i class="fa-light fa-arrows-rotate"></i>');
			if(response.status === 'success') {
				element.closest('.column-timeUp').find('span').html(response.data.timeUp);
			}
		});

		return false;
	}
}
class ProductsFeedHandle {
	constructor() {

		this.id = $('#js_productsFeed_id').val();

		this.productSearchModal = new bootstrap.Modal('#js_productsFeed_products_modal')

		this.productType = $('.js_productsFeed_input_type:checked').val();

		this.productTypeBox = $('.js_productsFeed_type_box');

		this.productTable = $('#js_productsFeed_products_result');

		this.ProductList = {
			products : [],
			add(product) {
				let objIndex = this.products.findIndex((item => item.id == product.id));
				if(objIndex == -1) {
					this.products.unshift(product);
				}
				return this.products;
			},
			update(product) {
				let objIndex = this.products.findIndex((item => item.id == product.id));
				this.products[objIndex] = {...this.products[objIndex], ...product};
				return this.products;
			},
			delete(productId) {
				productId = productId*1
				this.products = this.products.filter(function(item) {
					return item.id !== productId
				})
			},
		};

		if(this.id != undefined) {
			this.loadData();
		}
	}
	loadData() {
		let self = this;

		if(this.productType == 'productsCustom') {

			let data = {
				action : 'AdminProductFeedsAjax::productLoad',
				id : this.id
			}

			let self = this;

			request.post(ajax, data).then(function (response) {
				if (response.status === 'error') {
					SkilldoMessage.response(response)
				}
				if (response.status === 'success') {
					self.ProductList.products = response.data;
					self.productRender();
				}
			});
		}
	}
	changeProductType(element) {
		let productTypeNew = $('input[name="productType"]:checked').val();
		if(productTypeNew == 'category') {
			this.productTypeBox.find('.productsFeed_products_box').hide();
			this.productTypeBox.find('.productsFeed_categories').show();
		}
		if(productTypeNew == 'products') {
			this.productTypeBox.find('.productsFeed_products_box').hide();
		}
		if(productTypeNew == 'productsCustom') {
			this.productTypeBox.find('.productsFeed_products_box').hide();
			this.productTypeBox.find('.productsFeed_products').show();
		}
		this.productType = productTypeNew;
	}
	showProductModal(element) {
		this.productSearchModal.show();
		return false;
	}
	productSearch(element) {

		let data = {
			action      : 'AdminProductFeedsAjax::productSearch',
			categoryId  : $('select[name="search_category"]').val(),
			keyword     : $('input[name="search_name"]').val()
		}

		request.post(ajax, data).then(function( response ) {
			SkilldoMessage.response(response)
			if( response.status === 'success') {

				let listProduct = decodeURIComponent(atob(response.data).split('').map(function (c) {
					return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
				}).join(''));

				$('#js_productsFeed_products_search_result').html(listProduct).promise().done(function () {
					formBuilderReset();
				});
			}
		});

	}
	productAdd(element) {

		let self = this;

		$('#js_productsFeed_products_modal .js_productsFeed_product_search .select:checked').each(function () {
			let product = JSON.parse($(this).closest('.js_product_item').attr('data-item'));
			self.ProductList.add(product);
		});

		this.productRender();

		this.productSearchModal.hide();

		return false;
	}
	productRender() {
		this.productTable.html('');
		for (const [key, items_tmp] of Object.entries(this.ProductList.products)) {
			let items = [items_tmp];
			this.productTable.append(items.map(function(item) {
				item.price = number_format(item.price);
				item.price_sale = number_format(item.price_sale);
				return $('#product_template').html().split(/\$\{(.+?)\}/g).map(render(item)).join('');
			}));
		}
	}
	productDelete(element) {
		let id = element.attr('data-id');
		if(this.productType == 'productsCustom') {
			this.ProductList.delete(id);
		}
		element.closest('tr').remove();
		return false;
	}
	add(element) {

		$('.loading').show();

		let data = element.serializeJSON();

		data.action     =  'AdminProductFeedsAjax::add';

		data.products = [];

		for (const [key, item] of Object.entries(this.ProductList.products)) {
			data.products.unshift(item.id)
		}

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response)

			if(response.status === 'success') {
				window.location = response.data;
			}
		});

		return false;
	}
	save(element) {

		$('.loading').show();

		let data = element.serializeJSON();

		data.action     =  'AdminProductFeedsAjax::save';

		data.products = [];

		for (const [key, item] of Object.entries(this.ProductList.products)) {
			data.products.unshift(item.id)
		}

		request.post(ajax, data).then(function(response) {

			$('.loading').hide();

			SkilldoMessage.response(response)
		});

		return false;
	}
}

