<?php
class AdminProductFeedsAjax {
    static function productSearch($ci, $model): bool
    {
        $result['status'] = 'error';

        $result['message'] = 'Tìm sản phẩm không thành công';

        if(Request::post()) {

            $keyword    = trim((string)Request::post('keyword'));

            $categoryId = (int)trim((string)Request::post('categoryId'));

            $args = Qr::set('trash', 0)->where('public', 1)->select('id', 'title', 'image', 'price', 'price_sale')->orderBy('created');

            if(!empty($keyword)) {
                $args->where('title', 'like', '%'.$keyword.'%');
            }

            if(!empty($categoryId)) {
                $args->whereByCategory($categoryId);
            }

            $products = Product::gets($args);

            $result['data'] = '';

            foreach ($products as $product) {
                $product->image = Template::imgLink($product->image);
                $result['data'] .= Plugin::partial(PR_FEED_NAME, 'admin/views/product-item', ['item' => $product], true);
            }

            $result['data'] = base64_encode($result['data']);

            $result['status'] = 'success';

            $result['message'] = 'Load dữ liệu thành công';
        }

        echo json_encode( $result );

        return false;
    }
    static function add($ci, $model): bool
    {

        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if (!Auth::hasCap('productsFeedAdd')) {
            $result['message'] = 'Bạn không có quyền sử dụng chức năng này.';
            echo json_encode($result);
            return false;
        }

        if(Request::post()) {

            $feeds = [];

            if(empty(Request::post('name'))) {
                $result['message'] = 'Bạn chưa điền tên feed';
                echo json_encode($result);
                return false;
            }
            $feeds['name'] = trim(Request::post('name'));

            if(empty(Request::post('categoryGoogle'))) {
                $result['message'] = 'Bạn chưa chọn danh mục google.';
                echo json_encode($result);
                return false;
            }
            $feeds['categoryGoogle'] = trim(Request::post('categoryGoogle'));

            if(empty(Request::post('categoryFacebook'))) {
                $result['message'] = 'Bạn chưa chọn danh mục facebook.';
                echo json_encode($result);
                return false;
            }
            $feeds['categoryFacebook'] = trim(Request::post('categoryFacebook'));

            $type = Request::post('productType');

            if(empty($type)) {
                $result['message'] = __('Bạn chưa chọn loại sản phẩm sẽ áp dụng.');
                echo json_encode($result);
                return false;
            }

            if($type == 'category') {

                $categoryId = (int)Request::Post('categoryWebsite');

                if(empty($categoryId)) {
                    $result['message'] = __('Bạn chưa chọn danh mục sản phẩm sẽ lấy sản phẩm');
                    echo json_encode($result);
                    return false;
                }

                $feeds['categoryWebsite'] = $categoryId;
            }

            if($type == 'productsCustom') {

                $products = Request::Post('products');

                if(!have_posts($products)) {
                    $result['message'] = __('Bạn chưa chọn sản phẩm sẽ áp dụng');
                    echo json_encode($result);
                    return false;
                }

                $feeds['value'] = $products;
            }

            $feeds['type'] = $type;

            $feeds['key'] = uniqid();

            $error = PrFeed::insert($feeds);

            if(!is_skd_error($error)) {
                $result['status']   = 'success';
                $result['message']  = __('Lưu dữ liệu thành công.');
                $result['location'] = Url::admin('plugins?page=products-feed');
            }
        }

        echo json_encode($result);

        return true;
    }
    static function productLoad($ci, $model): bool
    {
        $result['status'] = 'error';

        $result['message'] = 'Tìm sản phẩm không thành công';

        if(Request::post()) {

            $id = (int)Request::Post('id');

            $feeds = PrFeed::get($id);

            if(!have_posts($feeds)) {
                $result['message'] = __('Danh sách này không tồn tại.');
                echo json_encode($result);
                return false;
            }

            $feeds->value = unserialize($feeds->value);

            if(!have_posts($feeds->value)) {
                $result['products'] = [];
                $result['status']   = 'success';
                $result['message']  = 'Load dữ liệu thành công';
                echo json_encode($result);
                return false;
            }

            $args = Qr::set('trash', 0)->where('public', 1)->whereIn('id', $feeds->value)->select('id', 'title', 'image');

            $products = Product::gets($args);

            foreach ($products as $product) {
                $product->image = Template::imgLink($product->image);
            }

            $result['products'] = $products;
            $result['status']   = 'success';
            $result['message']  = 'Load dữ liệu thành công';
        }

        echo json_encode( $result );

        return false;
    }
    static function save($ci, $model): bool
    {
        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if (!Auth::hasCap('productsFeedEdit')) {
            $result['message'] = 'Bạn không có quyền sử dụng chức năng này.';
            echo json_encode($result);
            return false;
        }

        if(Request::post()) {

            $id = (int)Request::Post('id');

            $object = PrFeed::get($id);

            if(!have_posts($object)) {
                $result['message'] = __('Danh sách này không tồn tại.');
                echo json_encode($result);
                return false;
            }

            $feeds = [];

            if(empty(Request::post('name'))) {
                $result['message'] = 'Bạn chưa điền tên feed';
                echo json_encode($result);
                return false;
            }
            $feeds['name'] = trim(Request::post('name'));

            if(empty(Request::post('categoryGoogle'))) {
                $result['message'] = 'Bạn chưa chọn danh mục google.';
                echo json_encode($result);
                return false;
            }
            $feeds['categoryGoogle'] = trim(Request::post('categoryGoogle'));

            if(empty(Request::post('categoryFacebook'))) {
                $result['message'] = 'Bạn chưa chọn danh mục facebook.';
                echo json_encode($result);
                return false;
            }
            $feeds['categoryFacebook'] = trim(Request::post('categoryFacebook'));

            $type = Request::post('productType');

            if(empty($type)) {
                $result['message'] = __('Bạn chưa chọn loại sản phẩm sẽ áp dụng.');
                echo json_encode($result);
                return false;
            }

            if($type == 'category') {

                $categoryId = (int)Request::Post('categoryWebsite');

                if(empty($categoryId)) {
                    $result['message'] = __('Bạn chưa chọn danh mục sản phẩm sẽ lấy sản phẩm');
                    echo json_encode($result);
                    return false;
                }

                $feeds['categoryWebsite'] = $categoryId;
            }

            if($type == 'productsCustom') {

                $products = Request::Post('products');

                if(!have_posts($products)) {
                    $result['message'] = __('Bạn chưa chọn sản phẩm sẽ áp dụng');
                    echo json_encode($result);
                    return false;
                }

                $feeds['value'] = $products;
            }

            $feeds['type'] = $type;

            $feeds['id'] = $object->id;

            $error = PrFeed::insert($feeds, $object);

            if(!is_skd_error($error)) {

                $result['status']   = 'success';
                $result['message']  = __('Lưu dữ liệu thành công.');
                $result['location'] = Url::admin('plugins?page=products-feed');
            }
        }

        echo json_encode($result);

        return true;
    }
    static function createdXML($ci, $model): bool
    {
        $result['status']  = 'error';

        $result['message'] = __('Lưu dữ liệu không thành công');

        if(Request::post()) {

            $id = (int)Request::Post('id');

            $object = PrFeed::get($id);

            if(!have_posts($object)) {
                $result['message'] = __('Danh sách này không tồn tại.');
                echo json_encode($result);
                return false;
            }

            $xml = PFeedHelper::createdXML($object);

            if(!empty($xml)) {
                $result['status']   = 'success';
                $result['message']  = __('Lưu dữ liệu thành công.');
                $result['timeUp'] = date('d-m-Y H:i');
            }
        }

        echo json_encode($result);

        return true;
    }
}

Ajax::admin('AdminProductFeedsAjax::productSearch');
Ajax::admin('AdminProductFeedsAjax::add');
Ajax::admin('AdminProductFeedsAjax::productLoad');
Ajax::admin('AdminProductFeedsAjax::save');
Ajax::admin('AdminProductFeedsAjax::createdXML');