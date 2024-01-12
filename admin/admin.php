<?php
include_once 'system.php';
include_once 'products.php';
include_once 'roles.php';
include_once 'ajax.php';

Class ProductsFeedAdmin {

    function __construct() {
        add_action('admin_init', 'ProductsFeedAdmin::navigation', 20);
        add_action('admin_init', 'ProductsFeedAdmin::assets', 50);
        add_action('action_bar_before', 'ProductsFeedAdmin::actionBar', 10);
    }

    static function assets(): void
    {
        $asset = Path::plugin(PR_FEED_NAME).'/assets/';
        if(Admin::is()) {
            Admin::asset()->location('footer')->add(PR_FEED_NAME, $asset.'script/script.admin.js');
        }
    }

    static function navigation(): void
    {
        if(Auth::hasCap('productsFeedList')) {
            if(class_exists('MarketingOnline')) {
                AdminMenu::addSub('marketing', 'products-feed', 'Products Feed', 'plugins?page=products-feed', ['callback' => 'ProductsFeedAdmin::page']);
            }
            else {
                AdminMenu::add('products-feed', 'Products Feed', 'plugins?page=products-feed', ['icon' => '<img src="'.Path::plugin(PR_FEED_NAME).'/thumb.png">', 'callback' => 'ProductsFeedAdmin::page']);
            }
        }
    }

    static function actionBar(): void
    {
        $view   = (empty(Request::get('view'))) ? 'index' : Request::get('view');

        $class  = Template::getClass();

        $page   = Request::get('page');

        if($class == 'plugins' && $page == 'products-feed') {
            echo '<div class="pull-left"></div>';
            echo '<div class="pull-right">';
            if($view == 'index') {
                if(Auth::hasCap('productsFeedAdd')) {
                    echo '<a href="'.Url::admin('plugins?page='.$page.'&view=add').'" class="btn btn-icon btn-green">'.Admin::icon('add').' Thêm chiến dịch</a>';
                }
            }
            if($view == 'add' || $view == 'edit') {
                if(Auth::hasCap('productsFeedEdit')) {
                    echo '<button name="save" class="btn btn-icon btn-green" form="js_productsFeed_form">' . Admin::icon('save') . ' Lưu</button>';
                }
                echo '<a href="'.Url::admin('plugins?page='.$page).'" class="btn btn-icon btn-blue">'.Admin::icon('back').' Quay lại</a>';
            }
            echo '</div>';
        }
    }

    static function page(): void
    {
        $view = Request::get('view');
        switch ($view) {
            case 'add': self::pageAdd(); break;
            case 'edit': self::pageEdit(); break;
            default: self::pageIndex(); break;
        }
    }

    static function pageIndex(): void
    {
        if (Auth::hasCap('productsFeedList')) {
            $productsFeed = PrFeed::gets();
            include_once 'views/index.php';
        }
        else {
            echo notice('error', 'Bạn không có quyền sử dụng chức năng này', false);
        }
    }

    static function pageAdd(): void
    {
        if (Auth::hasCap('productsFeedAdd')) {
            $productsCategories = ProductCategory::gets(Qr::set()->categoryType('options'));
            $googleCategories = PFeedHelper::config('categoriesGoogle');
            $facebookCategories = PFeedHelper::config('categoriesFacebook');
            include_once 'views/add.php';
        }
        else {
            echo notice('error', 'Bạn không có quyền sử dụng chức năng này', false);
        }
    }

    static function pageEdit(): void
    {
        if (Auth::hasCap('productsFeedEdit')) {
            $key = Request::get('key');
            $feed = PrFeed::get(Qr::set('key' , $key));
            if(have_posts($feed)) {
                $feed->value = unserialize($feed->value);
                $productsCategories = ProductCategory::gets(Qr::set()->categoryType('options'));
                $googleCategories = PFeedHelper::config('categoriesGoogle');
                $facebookCategories = PFeedHelper::config('categoriesFacebook');
                include_once 'views/edit.php';
            }
        }
        else {
            echo notice('error', 'Bạn không có quyền sử dụng chức năng này', false);
        }
    }
}

new ProductsFeedAdmin();