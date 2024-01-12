<?php
Class AdminProductsFeedRoles {
    static function group($group) {
        $group['ProductsFeed'] = [
            'label' => __('Products Feed'),
            'capabilities' => array_keys(AdminProductsFeedRoles::capabilities())
        ];
        return $group;
    }
    static function label($label): array
    {
        return array_merge($label, AdminProductsFeedRoles::capabilities() );
    }
    static function capabilities(): array
    {
        $label['productsFeedList']     = 'Xem chiến dịch Feed';
        $label['productsFeedAdd']      = 'Thêm chiến dịch Feed';
        $label['productsFeedEdit']     = 'Cập nhật chiến dịch Feed';
        $label['productsFeedDelete']   = 'Xóa chiến dịch Feed';
        return $label;
    }
}

add_filter('user_role_editor_group', 'AdminProductsFeedRoles::group' );
add_filter('user_role_editor_label', 'AdminProductsFeedRoles::label' );