<?php

use JetBrains\PhpStorm\NoReturn;
use SkillDo\Validate\Rule;

class AdminProductFeedsAjax {
    #[NoReturn]
    static function load(\SkillDo\Http\Request $request): void
    {
        if($request->isMethod('post')) {

            $page    = $request->input('page');

            $page   = (is_null($page) || empty($page)) ? 1 : (int)$page;

            $limit  = $request->input('limit');

            $limit   = (is_null($limit) || empty($limit)) ? 10 : (int)$limit;

            $keyword = $request->input('keyword');

            $recordsTotal   = $request->input('recordsTotal');

            $args = Qr::set();

            if (!empty($keyword)) {
                $args->where('name', 'like', '%' . $keyword . '%');
            }
            /**
             * @since 7.0.0
             */
            $args = apply_filters('admin_products_feed_controllers_index_args_before_count', $args);

            if(!is_numeric($recordsTotal)) {
                $recordsTotal = apply_filters('admin_products_feed_controllers_index_count', PrFeed::count($args), $args);
            }

            # [List data]
            $args->limit($limit)
                ->offset(($page - 1)*$limit)
                ->orderBy('order')
                ->orderBy('created', 'desc');

            $args = apply_filters('admin_products_feed_controllers_index_args', $args);

            $objects = apply_filters('admin_products_feed_controllers_index_objects', PrFeed::gets($args), $args);

            $args = [
                'items' => $objects,
                'table' => 'products_feed',
                'model' => model('products_feed'),
                'module'=> 'products_feed',
            ];

            $table = new AdminProductsFeedTable($args);
            $table->get_columns();
            ob_start();
            $table->display_rows_or_message();
            $html = ob_get_contents();
            ob_end_clean();

            /**
             * Bulk Actions
             * @hook table_*_bulk_action_buttons Hook mới phiên bản 7.0.0
             */
            $buttonsBulkAction = apply_filters('table_products_feeds_bulk_action_buttons', []);

            $bulkAction = Admin::partial('include/table/header/bulk-action-buttons', [
                'actionList' => $buttonsBulkAction
            ]);

            $result['data'] = [
                'html'          => base64_encode($html),
                'bulkAction'    => base64_encode($bulkAction),
            ];
            $result['pagination']   = [
                'limit' => $limit,
                'total' => $recordsTotal,
                'page'  => (int)$page,
            ];

            response()->success(trans('ajax.load.success'), $result);
        }

        response()->error(trans('ajax.load.error'));
    }
    #[NoReturn]
    static function productSearch(\SkillDo\Http\Request $request): void
    {
        if($request->isMethod('post')) {

            $keyword    = trim((string)$request->input('keyword'));

            $categoryId = (int)trim((string)$request->input('categoryId'));

            $args = Qr::set('trash', 0)->where('public', 1)->select('id', 'title', 'image', 'price', 'price_sale')->orderBy('created');

            if(!empty($keyword)) {
                $args->where('title', 'like', '%'.$keyword.'%');
            }

            if(!empty($categoryId)) {
                $args->whereByCategory($categoryId);
            }

            $products = Product::gets($args);

            $result = '';

            foreach ($products as $product) {
                $product->image = Template::imgLink($product->image);
                $result .= Plugin::partial(PR_FEED_NAME, 'views/product-item', ['item' => $product]);
            }

            response()->success(trans('ajax.load.success'), base64_encode($result));
        }

        response()->error(trans('Tìm sản phẩm không thành công'));
    }
    #[NoReturn]
    static function add(\SkillDo\Http\Request $request): void
    {
        if (!Auth::hasCap('productsFeedAdd')) {
            response()->error(trans('Bạn không có quyền sử dụng chức năng này'));
        }

        if($request->isMethod('post')) {

            $feeds = [];

            $validate = $request->validate([
                'name' => Rule::make('Tên feed')->notEmpty(),
                'categoryGoogle' => Rule::make('Danh mục google')->notEmpty(),
                'categoryFacebook' => Rule::make('Danh mục facebook')->notEmpty(),
                'productType' => Rule::make('loại sản phẩm sẽ áp dụng')->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $feeds['name'] = trim($request->input('name'));

            $feeds['categoryGoogle'] = trim($request->input('categoryGoogle'));

            $feeds['categoryFacebook'] = trim($request->input('categoryFacebook'));

            $type = $request->input('productType');

            if($type == 'category') {

                $categoryId = (int)$request->input('categoryWebsite');

                if(empty($categoryId)) {
                    response()->error(trans('Bạn chưa chọn danh mục sản phẩm sẽ lấy sản phẩm'));
                }

                $feeds['categoryWebsite'] = $categoryId;
            }

            if($type == 'productsCustom') {

                $products = $request->input('products');

                if(!have_posts($products)) {
                    response()->error(trans('Bạn chưa chọn sản phẩm sẽ áp dụng'));
                }

                $feeds['value'] = $products;
            }

            $feeds['type'] = $type;

            $feeds['key'] = uniqid();

            $error = PrFeed::insert($feeds);

            if(!is_skd_error($error)) {
                response()->success(trans('ajax.save.success'), Url::admin('plugins/products-feed'));
            }
        }

        response()->error(trans('ajax.add.error'));
    }
    #[NoReturn]
    static function productLoad(\SkillDo\Http\Request $request): void
    {
        if($request->isMethod('post')) {

            $id = (int)$request->input('id');

            $feeds = PrFeed::get($id);

            if(!have_posts($feeds)) {
                response()->error(trans('danh sách này không tồn tại'));
            }

            $feeds->value = unserialize($feeds->value);

            if(!have_posts($feeds->value)) {
                response()->success(trans('ajax.load.success'));
            }

            $args = Qr::set('trash', 0)->where('public', 1)->whereIn('id', $feeds->value)->select('id', 'title', 'image');

            $products = Product::gets($args);

            foreach ($products as $product) {
                $product->image = Template::imgLink($product->image);
            }

            response()->success(trans('ajax.load.success'), $products);
        }

        response()->error(trans('ajax.load.error'));
    }
    #[NoReturn]
    static function save(\SkillDo\Http\Request $request): void
    {
        if (!Auth::hasCap('productsFeedEdit')) {
            response()->error(trans('Bạn không có quyền sử dụng chức năng này'));
        }

        if($request->isMethod('post')) {

            $id = (int)$request->input('id');

            $object = PrFeed::get($id);

            if(!have_posts($object)) {
                response()->error(trans('Danh sách này không tồn tại'));
            }

            $feeds = [];

            $validate = $request->validate([
                'name' => Rule::make('Tên feed')->notEmpty(),
                'categoryGoogle' => Rule::make('Danh mục google')->notEmpty(),
                'categoryFacebook' => Rule::make('Danh mục facebook')->notEmpty(),
                'productType' => Rule::make('loại sản phẩm sẽ áp dụng')->notEmpty(),
            ]);

            if ($validate->fails()) {
                response()->error($validate->errors());
            }

            $feeds['name'] = trim($request->input('name'));

            $feeds['categoryGoogle'] = trim($request->input('categoryGoogle'));

            $feeds['categoryFacebook'] = trim($request->input('categoryFacebook'));

            $type = $request->input('productType');

            if($type == 'category') {

                $categoryId = (int)$request->input('categoryWebsite');

                if(empty($categoryId)) {
                    response()->error(trans('Bạn chưa chọn danh mục sản phẩm sẽ lấy sản phẩm'));
                }

                $feeds['categoryWebsite'] = $categoryId;
            }

            if($type == 'productsCustom') {

                $products = $request->input('products');

                if(!have_posts($products)) {
                    response()->error(trans('Bạn chưa chọn sản phẩm sẽ áp dụng'));
                }

                $feeds['value'] = $products;
            }

            $feeds['type'] = $type;

            $feeds['id'] = $object->id;

            $error = PrFeed::insert($feeds, $object);

            if(!is_skd_error($error)) {

                response()->success(trans('ajax.save.success'), Url::admin('plugins/products-feed'));
            }
        }

        response()->error(trans('ajax.save.error'));
    }
    #[NoReturn]
    static function createdXML(\SkillDo\Http\Request $request): void
    {
        if($request->isMethod('post')) {

            $id = (int)$request->input('id');

            $object = PrFeed::get($id);

            if(!have_posts($object)) {
                response()->error(trans('Danh sách này không tồn tại'));
            }

            $xml = PFeedHelper::createdXML($object);

            if(!empty($xml)) {
                response()->success(trans('ajax.save.success'), ['timeUp' => date('d-m-Y H:i')]);
            }
        }

        response()->error(trans('ajax.save.error'));
    }
}

Ajax::admin('AdminProductFeedsAjax::load');
Ajax::admin('AdminProductFeedsAjax::productSearch');
Ajax::admin('AdminProductFeedsAjax::add');
Ajax::admin('AdminProductFeedsAjax::productLoad');
Ajax::admin('AdminProductFeedsAjax::save');
Ajax::admin('AdminProductFeedsAjax::createdXML');