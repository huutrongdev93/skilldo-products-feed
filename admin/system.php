<?php
Class AdminProductsFeedSetting {
    static function register($tabs) {
        $tabs['ProductsFeed']   = [
            'label'       => 'Shopping Feed',
            'description' => 'Quản lý cấu hình tạo file xml google shopping, facebook shopping',
            'callback'    => 'AdminProductsFeedSetting::render',
            'icon'        => '<i class="fa-brands fa-google"></i>',
        ];
        return $tabs;
    }
    static function render($ci, $tab): void
    {
        $config = PFeedHelper::config();
        do_action('admin_system_ProductsFeed_html', $config, $tab);
    }
    static function renderDefault($config): void {

        $attributes = Attributes::gets(Qr::set()->select('id', 'title'));

        $attributeOptions = ['' => 'Thuộc tính'];

        foreach ($attributes as $attribute) {
            $attributeOptions[$attribute->id] = $attribute->title;
        }

        $formBarcode = new FormBuilder();

        $formBarcode
            ->add('productsFeed[categoriesGoogle]', 'select2-multiple', [
                'label' => 'Danh mục google shipping',
                'note' => 'Danh mục google shipping sẽ được sử dụng trong website',
                'options' => PFeedHelper::categoryGoogle()
            ], $config['categoriesGoogle'])
            ->add('productsFeed[categoriesFacebook]', 'select2-multiple', [
                'label' => 'Danh mục facebook shipping',
                'note' => 'Danh mục facebook shipping sẽ được sử dụng trong website',
                'options' => PFeedHelper::categoryFacebook()
            ], $config['categoriesFacebook'])
            ->add('productsFeed[id]', 'select', [
                'label'     => 'Mã nhận dạng sản phẩm',
                'options'   => [
                    'code' => 'Lấy từ mã sản phẩm',
                    'id'   => 'Lấy từ id sản phẩm (mã hệ thống tự tạo)',
                ],
                'start' => 4
            ], $config['id'])
            ->add('productsFeed[availability]', 'select', [
                'label'     => 'Tình trạng còn hàng của sản phẩm',
                'options'   => PFeedHelper::availability(),
                'start' => 4
            ], $config['availability'])
            ->add('productsFeed[condition]', 'select', [
                'label'     => 'Tình trạng thời điểm bán',
                'options'   => [
                    'new'           => 'Sản phẩm mới',
                    'used'          => 'Đã qua sử dụng',
                    'refurbished'   => 'Đã được tân trang',
                ],
                'start' => 4
            ], $config['condition'])
            ->add('productsFeed[color]', 'select', [
                'label'     => 'Tùy chọn cho Màu (Color)',
                'options'   => $attributeOptions,
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 4
            ], $config['color'])
            ->add('productsFeed[size]', 'select', [
                'label'     => 'Tùy chọn cho Size',
                'options'   => $attributeOptions,
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 4
            ], $config['size']);

        Admin::partial('function/system/html/default', [
            'title'       => 'Cấu hình Products Feed',
            'description' => 'Quản lý cấu hình products feed mặc định của sản phẩm',
            'form'        => $formBarcode
        ]);
    }
    static function save($result, $data) {
        if(isset($data['productsFeed'])) {
            $config = PFeedHelper::config();
            $config['categoriesGoogle'] = [];
            if(!empty($data['productsFeed']['categoriesGoogle'])) {
                $config['categoriesGoogle'] = Str::clear($data['productsFeed']['categoriesGoogle']);
            }
            $config['categoriesFacebook'] = [];
            if(!empty($data['productsFeed']['categoriesFacebook'])) {
                $config['categoriesFacebook'] = Str::clear($data['productsFeed']['categoriesFacebook']);
            }
            $config['id']  = Str::clear($data['productsFeed']['id']);
            $config['availability'] = Str::clear($data['productsFeed']['availability']);
            $config['condition']    = Str::clear($data['productsFeed']['condition']);
            $config['color']    = Str::clear($data['productsFeed']['color']);
            $config['size']    = Str::clear($data['productsFeed']['size']);
            Option::update('ProductsFeedConfig', $config);
        }
        return $result;
    }
}

add_filter('skd_system_tab' , 'AdminProductsFeedSetting::register', 20);
add_action('admin_system_ProductsFeed_html', 'AdminProductsFeedSetting::renderDefault', 10);
add_filter('admin_system_ProductsFeed_save', 'AdminProductsFeedSetting::save',10,2);