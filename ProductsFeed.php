<?php
/**
 * Plugin name     : Products Feed
 * Plugin class    : ProductsFeed
 * Plugin uri      : http://sikido.vn
 * Description     : Ứng dụng hỗ trợ nguồn cung cấp dữ liệu sản phẩm để thêm vào chạy Google Shopping Ads và Facebook Ads
 * Author          : SKDSoftware Dev Team
 * Version         : 1.1.0
 */

const PR_FEED_NAME = 'ProductsFeed';

Class ProductsFeed {

    private string $name = 'ProductsFeed';

    public function __construct() {
        $this->loadDependencies();
    }

    public function active(): void
    {
        ProductsFeedActivator::activate();
    }

    public function uninstall(): void
    {
        ProductsFeedDeactivate::uninstall();
    }

    private function loadDependencies(): void
    {
        $path = Path::plugin(PR_FEED_NAME);
        require_once $path.'/active.php';
        require_once $path.'/function.php';
        require_once $path.'/admin/admin.php';
    }
}

new ProductsFeed();