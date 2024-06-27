<?php
class AdminProductsFeedProduct {
    static function addMetaBox(): void {
        Metabox::add('productsFeed', 'Feed', 'AdminProductsFeedProduct::render', [
            'module'    => 'products',
            'position'  => 10,
            'content'   => 'tabs'
        ]);
    }
    static function render($object): void
    {
        $attributes = Attributes::gets(Qr::set()->select('id', 'title'));

        $attributeOptions = ['' => 'Thuộc tính'];

        foreach ($attributes as $attribute) {
            $attributeOptions[$attribute->id] = $attribute->title;
        }

        $productsFeed = [
            'categoryGoogle'    => '',
            'categoryFacebook'  => '',
            'availability'      => PFeedHelper::config('availability'),
            'condition'         => PFeedHelper::config('condition'),
            'color'             => '',
            'size'              => '',
            'gender'            => '',
            'productHighlight'  => '',
            'productDetail'     => '',
        ];

        if(have_posts($object)) {
            $productsFeedData = Product::getMeta($object->id, 'productsFeed', true);
            if(!empty($productsFeedData['categoryGoogle'])) $productsFeed['categoryGoogle'] = $productsFeedData['categoryGoogle'];
            if(!empty($productsFeedData['categoryFacebook'])) $productsFeed['categoryFacebook'] = $productsFeedData['categoryFacebook'];
            if(!empty($productsFeedData['availability'])) $productsFeed['availability'] = $productsFeedData['availability'];
            if(!empty($productsFeedData['condition'])) $productsFeed['condition'] = $productsFeedData['condition'];
            if(!empty($productsFeedData['productHighlight'])) $productsFeed['productHighlight'] = $productsFeedData['productHighlight'];
            if(!empty($productsFeedData['productDetail'])) $productsFeed['productDetail'] = $productsFeedData['productDetail'];
            if(!empty($productsFeedData['color'])) $productsFeed['color'] = $productsFeedData['color'];
            if(!empty($productsFeedData['size'])) $productsFeed['size'] = $productsFeedData['size'];
            if(!empty($productsFeedData['gender'])) $productsFeed['gender'] = $productsFeedData['gender'];
        }

        $formGoogle = form();
        $formGoogle
            ->add('productsFeed[categoryGoogle]', 'select', [
                'label'     => 'Danh mục Google',
                'options'   => PFeedHelper::config('categoriesGoogle'),
                'start' => 5
            ], $productsFeed['categoryGoogle'])
            ->add('productsFeed[categoryFacebook]', 'select', [
                'label'     => 'Danh mục Facebook',
                'options'   => PFeedHelper::config('categoriesFacebook'),
                'start' => 4
            ], $productsFeed['categoryFacebook'])
            ->add('productsFeed[availability]', 'select', [
                'label'     => 'Tình trạng còn hàng',
                'options'   => PFeedHelper::availability(),
                'start' => 3
            ], $productsFeed['availability'])
            ->add('productsFeed[condition]', 'select', [
                'label'     => 'Tình trạng thời điểm bán',
                'options'   => [
                    'new'           => 'Sản phẩm mới',
                    'used'          => 'Đã qua sử dụng',
                    'refurbished'   => 'Đã được tân trang',
                ],
                'start' => 3
            ], $productsFeed['condition'])
            ->add('productsFeed[color]', 'select', [
                'label'     => 'Tùy chọn cho Màu (Color)',
                'options'   => $attributeOptions,
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 3
            ], $productsFeed['color'])
            ->add('productsFeed[size]', 'select', [
                'label'     => 'Tùy chọn cho Size',
                'options'   => $attributeOptions,
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 3
            ], $productsFeed['size'])
            ->add('productsFeed[gender]', 'select', [
                'label'     => 'Giới tính',
                'options'   => [
                    '' => 'Chọn giới tính',
                    'male' => 'Nam',
                    'female' => 'Nữ',
                    'unisex' => 'Trung tính',
                ],
                'note' => 'Bắt buộc đối với tất cả các sản phẩm hàng may mặc',
                'start' => 3
            ], $productsFeed['size'])
            ->add('productsFeed[productHighlight]', 'textarea', [
                'label' => 'Đặc điểm nổi bật của sản phẩm',
                'note'  => 'Thêm càng nhiều điểm nổi bật càng tốt để giúp khách hàng đọc lướt những đoạn giải đáp các câu hỏi phổ biến nhất hoặc nêu ra các khía cạnh quan trọng nhất của sản phẩm
                <br />Ví dụ: "Màn hình LED mới", "Độ phân giải 4K (2160p)"'
            ], $productsFeed['productHighlight'])
            ->add('productsFeed[productDetail]', 'textarea', [
                'label' => 'Thông số kỹ thuật hoặc chi tiết bổ sung',
                'note'  => 'Thêm càng nhiều chi tiết càng tốt để giúp giải đáp những điều khách hàng có thể thắc mắc về sản phẩm của bạn. Nhập chi tiết theo định dạng sau: "section_name: attribute_name: attribute_value"
                <br /> Ví dụ: "Loại màn hình:LED:mới", "Màn hình:Độ phân giải:4K (2160p)"'
            ], $productsFeed['productDetail']);

        $formGoogle->html(false);
    }
    static function save($productId, $module): void
    {
        if($module == 'products' && have_posts(request()->input('productsFeed'))) {
            $request    = request()->input('productsFeed');
            $productsFeed = [
                'categoryGoogle'    => (!empty($request['categoryGoogle'])) ? $request['categoryGoogle'] : '',
                'categoryFacebook'  => (!empty($request['categoryFacebook'])) ? $request['categoryFacebook'] : '',
                'availability'      => $request['availability'],
                'condition'         => $request['condition'],
                'productHighlight'  => $request['productHighlight'],
                'productDetail'     => $request['productDetail'],
                'color'             => $request['color'],
                'size'              => $request['size'],
                'gender'            => $request['gender'],
            ];
            Product::updateMeta($productId, 'productsFeed', $productsFeed);
        }
    }
}

add_action('init', 'AdminProductsFeedProduct::addMetaBox');
add_action('save_object', 'AdminProductsFeedProduct::save', 10, 2);