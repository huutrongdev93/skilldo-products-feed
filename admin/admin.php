<?php
include_once 'system.php';
include_once 'products.php';
include_once 'roles.php';
include_once 'ajax.php';
include_once 'table.php';
include_once 'button.php';

Class ProductsFeedAdmin {

    function __construct() {
        add_action('admin_init', 'ProductsFeedAdmin::navigation', 20);
        add_action('admin_init', 'ProductsFeedAdmin::assets', 50);
    }

    static function assets(): void
    {
        $asset = Path::plugin(PR_FEED_NAME).'/assets/';
        if(Admin::is()) {
            Admin::asset()->location('footer')->add(PR_FEED_NAME, $asset.'script/script.admin.js');
            add_filter('manage_products_feed_input', 'ProductsFeedAdmin::form');
            add_filter('form_submit_products_feed', 'ProductsFeedAdmin::save', 10, 2);
        }
    }

    static function navigation(): void
    {
        if(Auth::hasCap('productsFeedList')) {
            AdminMenu::addSub('marketing', 'products-feed', 'Products Feed', 'plugins/products-feed', ['callback' => 'ProductsFeedAdmin::page']);
        }
    }

    static function page(\SkillDo\Http\Request $request, $params): void {

        $view = $params[0] ?? '';

        switch ($view) {
            case 'add':
                self::pageAdd($request);
                break;
            case 'edit':
                self::pageEdit($request, $params);
                break;
            default:
                self::pageList($request);
                break;
        }
    }

    static function pageList(\SkillDo\Http\Request $request): void
    {
        $table = new AdminProductsFeedTable([
            'items' => [],
            'table' => 'products_feed',
            'model' => model('products_feed'),
            'module'=> 'products_feed',
        ]);

        Admin::view('components/page-default/page-index', [
            'module'    => 'products_feed',
            'name'      => 'Products Feed',
            'table'     => $table,
            'tableId'   => 'admin_table_products_feed_list',
            'limitKey'  => 'admin_products_feed_limit',
            'ajax'      => 'AdminProductFeedsAjax::load',
        ]);

        Plugin::view(PR_FEED_NAME, 'views/index');
    }

    static function pageAdd(\SkillDo\Http\Request $request): void
    {
        if (Auth::hasCap('productsFeedAdd')) {

            $productsCategories = ProductCategory::gets(Qr::set()->categoryType('options'));

            $googleCategories = PFeedHelper::config('categoriesGoogle');

            $facebookCategories = PFeedHelper::config('categoriesFacebook');

            Plugin::view(PR_FEED_NAME, 'views/add', [
                'productsCategories' => $productsCategories,
                'googleCategories' => $googleCategories,
                'facebookCategories' => $facebookCategories
            ]);
        }
        else {
            echo Admin::alert('error', 'Bạn không có quyền sử dụng chức năng này');
        }
    }

    static function pageEdit(\SkillDo\Http\Request $request, $params): void
    {
        if (Auth::hasCap('productsFeedEdit')) {

            $key  = $params[1] ?? 0;

            $feed = PrFeed::get(Qr::set('key' , $key));

            if(have_posts($feed)) {

                $feed->value = unserialize($feed->value);

                $productsCategories = ProductCategory::gets(Qr::set()->categoryType('options'));

                $googleCategories = PFeedHelper::config('categoriesGoogle');

                $facebookCategories = PFeedHelper::config('categoriesFacebook');

                Plugin::view(PR_FEED_NAME, 'views/edit', [
                    'productsCategories' => $productsCategories,
                    'googleCategories' => $googleCategories,
                    'facebookCategories' => $facebookCategories,
                    'feed' => $feed
                ]);
            }
        }
        else {
            echo Admin::alert('error', 'Bạn không có quyền sử dụng chức năng này');
        }
    }
}

new ProductsFeedAdmin();