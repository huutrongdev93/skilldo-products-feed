<?php
Class AdminProductsFeedSetting {
    static function register($tabs) {
        $tabs['ProductsFeed']   = [
            'group'       => 'marketing',
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

        $formBarcode = form();

        $formBarcode
            ->select2('productsFeed[categoriesGoogle]', PFeedHelper::categoryGoogle(), [
                'label' => 'Danh mục google shipping',
                'note' => 'Danh mục google shipping sẽ được sử dụng trong website',
                'multiple' => true,
            ], $config['categoriesGoogle'])
            ->select2('productsFeed[categoriesFacebook]', PFeedHelper::categoryFacebook(), [
                'label' => 'Danh mục facebook shipping',
                'note' => 'Danh mục facebook shipping sẽ được sử dụng trong website',
                'multiple' => true,
            ], $config['categoriesFacebook'])
            ->select('productsFeed[id]', [
                'code' => 'Lấy từ mã sản phẩm',
                'id'   => 'Lấy từ id sản phẩm (mã hệ thống tự tạo)',
            ], [
                'label' => 'Mã nhận dạng sản phẩm',
                'start' => 4
            ], $config['id'])
            ->select('productsFeed[availability]', PFeedHelper::availability(), [
                'label'     => 'Tình trạng còn hàng của sản phẩm',
                'start' => 4
            ], $config['availability'])
            ->select('productsFeed[condition]', [
                'new'           => 'Sản phẩm mới',
                'used'          => 'Đã qua sử dụng',
                'refurbished'   => 'Đã được tân trang',
            ], [
                'label' => 'Tình trạng thời điểm bán',
                'start' => 4
            ], $config['condition'])
            ->select('productsFeed[color]', $attributeOptions, [
                'label'     => 'Tùy chọn cho Màu (Color)',
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 4
            ], $config['color'])
            ->select('productsFeed[size]', $attributeOptions, [
                'label'     => 'Tùy chọn cho Size',
                'note'      => 'Dùng cho tất cả các sản phẩm hàng may mặc',
                'start' => 4
            ], $config['size']);

        Admin::view('components/system-default', [
            'title'       => 'Cấu hình Products Feed',
            'description' => 'Quản lý cấu hình products feed mặc định của sản phẩm',
            'form'        => $formBarcode
        ]);
    }
    static function save(\SkillDo\Http\Request $request): void
    {
        if($request->has('productsFeed')) {
            $productsFeed = $request->input('productsFeed');
            $config = PFeedHelper::config();
            $config['categoriesGoogle'] = [];
            if(!empty($productsFeed['categoriesGoogle'])) {
                $config['categoriesGoogle'] = Str::clear($productsFeed['categoriesGoogle']);
            }
            $config['categoriesFacebook'] = [];
            if(!empty($productsFeed['categoriesFacebook'])) {
                $config['categoriesFacebook'] = Str::clear($productsFeed['categoriesFacebook']);
            }
            $config['id']  = Str::clear($productsFeed['id']);
            $config['availability'] = Str::clear($productsFeed['availability']);
            $config['condition']    = Str::clear($productsFeed['condition']);
            $config['color']    = Str::clear($productsFeed['color']);
            $config['size']    = Str::clear($productsFeed['size']);
            Option::update('ProductsFeedConfig', $config);
        }
    }
}

add_filter('skd_system_tab' , 'AdminProductsFeedSetting::register', 20);
add_action('admin_system_ProductsFeed_html', 'AdminProductsFeedSetting::renderDefault', 10);
add_action('admin_system_ProductsFeed_save', 'AdminProductsFeedSetting::save');