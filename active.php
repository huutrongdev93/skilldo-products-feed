<?php
Class ProductsFeedActivator {
    public static function activate(): void
    {
        self::createTable();
        self::createPage();
        self::addOption();
        self::addRole();
    }
    public static function createTable(): void
    {
        if(!schema()->hasTable('products_feed')) {
            schema()->create('products_feed', function ($table) {
                $table->increments('id');
                $table->string('name', 255)->collate('utf8mb4_unicode_ci');
                $table->string('key', 255)->collate('utf8mb4_unicode_ci');
                $table->string('categoryGoogle', 255)->collate('utf8mb4_unicode_ci');
                $table->string('categoryFacebook', 255)->collate('utf8mb4_unicode_ci');
                $table->integer('categoryWebsite')->default(0);
                $table->string('type', 255)->collate('utf8mb4_unicode_ci');
                $table->text('value')->collate('utf8mb4_unicode_ci');
                $table->integer('timeUp')->default(0);
                $table->integer('order')->default(0);
                $table->dateTime('created');
                $table->dateTime('updated')->nullable();
            });
        }
    }
    public static function createPage(): void
    {
        $model = model('routes');
        //add sitemap to router
        $count = $model::where('slug', 'products-feed.xml')->where('plugin', 'ProductsFeed')->amount();
        if($count == 0) {
            $model->add(array(
                'slug'        => 'products-feed.xml',
                'controller'  => 'frontend/home/page/',
                'plugin'      => 'ProductsFeed',
                'object_type' => 'ProductsFeed',
                'directional' => 'ProductsFeed',
                'callback' 	  => 'ProductsFeedXml',
            ));
        }
    }
    public static function addOption() { }
    public static function addRole(): void
    {
        $role = Role::make()->get('root');
        $role->add('productsFeedList');
        $role->add('productsFeedAdd');
        $role->add('productsFeedEdit');
        $role->add('productsFeedDelete');

        $role = Role::make()->get('administrator');
        $role->add('productsFeedList');
        $role->add('productsFeedAdd');
        $role->add('productsFeedEdit');
        $role->add('productsFeedDelete');
    }
}

Class ProductsFeedDeactivate {
    public static function uninstall(): void
    {
        self::cropTable();
        self::deletePage();
        self::deleteOption();
        self::deleteData();
    }
    public static function cropTable(): void
    {
        schema()->drop('products_feed');
    }
    public static function deletePage(): void
    {
        $model = model('routes');
        //add sitemap to router
        $count = $model->count(Qr::set('slug', 'products-feed.xml')->where('plugin', 'ProductsFeed'));
        if($count != 0) {
            $model->delete(Qr::set('slug', 'product-feed.xml')->where('plugin', 'ProductsFeed'));
        }
    }
    public static function deleteOption() {}
    public static function deleteData() {}
}