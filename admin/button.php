<?php
class AdminProductsFeedButton {
    /**
     * Thêm buttons action cho header của table
     * @param $buttons
     * @return array
     */
    static function tableHeaderButton($buttons): array
    {
        $buttons[] = Admin::button('add', ['href' => Url::admin('plugins/products-feed/add')]);
        $buttons[] = Admin::button('reload');
        return $buttons;
    }

    /**
     * Thêm buttons cho hành dộng hàng loạt
     * @param array $actionList
     * @return array
     */
    static function bulkAction(array $actionList): array
    {
        return $actionList;
    }

    /**
     * Thêm button cho trang thêm mới edit
     * @param $module
     * @return void
     */
    static function formButton($module): void
    {
        $buttons = [];

        $view = Url::segment(4);

        if($view == 'add') {
            $buttons[] = Admin::button('save');
            $buttons[] = Admin::button('back', ['href' => Url::admin('plugins/products-feed')]);
        }

        if($view == 'edit') {
            if(Auth::hasCap('productsFeedEdit')) {
                $buttons[] = Admin::button('save', ['form' => 'js_productsFeed_form']);
            }
            $buttons[] = Admin::button('add', ['href' => Url::admin('plugins/products-feed/add'), 'text' => '', 'tooltip' => trans('button.add')]);
            $buttons[] = Admin::button('back', ['href' => Url::admin('plugins/products-feed'), 'text' => '', 'tooltip' => trans('button.back')]);
        }

        $buttons = apply_filters('products_feed_form_buttons', $buttons);

        Admin::view('include/form/form-action', ['buttons' => $buttons, 'module' => $module]);
    }
}
add_filter('table_products_feed_header_buttons', 'AdminProductsFeedButton::tableHeaderButton');
add_filter('table_products_feed_bulk_action_buttons', 'AdminProductsFeedButton::bulkAction', 30);