<?php

use SkillDo\Form\Form;
use SkillDo\Table\SKDObjectTable;
use SkillDo\Http\Request;

class AdminProductsFeedTable extends SKDObjectTable {

    function get_columns() {
        $this->_column_headers = [];
        $this->_column_headers['cb']       = 'cb';
        $this->_column_headers['name']     = [
            'label' => trans('Tên chương trình'),
            'column' => fn($item, $args) => \SkillDo\Table\Columns\ColumnText::make('name', $item, $args)
        ];
        $this->_column_headers['xml']     = [
            'label' => trans('Link XML'),
            'column' => fn($item, $args) => \SkillDo\Table\Columns\ColumnView::make('xml', $item, $args)
            ->html(function(\SkillDo\Table\Columns\ColumnView $column) {
                Plugin::view(PR_FEED_NAME, 'views/table/xml', ['feed' => $column->item]);
            })
        ];
        $this->_column_headers['category']     = [
            'label' => trans('Loại'),
            'column' => fn($item, $args) => \SkillDo\Table\Columns\ColumnText::make('category', $item, $args)
                ->value(function($item) {
                    return match ($item->type) {
                        'category' => 'Danh mục',
                        'products' => 'Tất cả sản phẩm',
                        'productsCustom' => 'Sản phẩm tùy chọn',
                    };
                })
        ];
        $this->_column_headers['created']    = trans('table.created');
        $this->_column_headers['timeUp']     = [
            'label' => trans('Ngày Cập nhật'),
            'column' => fn($item, $args) => \SkillDo\Table\Columns\ColumnView::make('timeUp', $item, $args)
                ->html(function(\SkillDo\Table\Columns\ColumnView $column) {
                    Plugin::view(PR_FEED_NAME, 'views/table/time-up', ['feed' => $column->item]);
                })
        ];
        $this->_column_headers['action']   = trans('table.action');
        return apply_filters( "manage_products_feed_columns", $this->_column_headers );
    }

    function actionButton($item, $module, $table): array
    {
        $listButton = [];

        if (\Auth::hasCap('productsFeedEdit')) {
            $listButton[] = Admin::button('blue', [
                'href' => Url::admin('plugins/products-feed/edit/'.$item->key),
                'icon' => Admin::icon('edit')
            ]);
        }

       if (\Auth::hasCap('productsFeedDelete')) {
           $listButton[] = Admin::btnDelete([
               'id' => $item->id,
               'model' => 'PrFeed',
               'description' => 'Bạn chắc chắn muốn xóa '.$item->name.' ?'
           ]);
       }
        /**
         * @since 7.0.0
         */
        return apply_filters('admin_products_feed_table_columns_action', $listButton);
    }

    function headerFilter(Form $form, Request $request)
    {
        /**
         * @singe v7.0.0
         */
        return apply_filters('admin_products_feed_table_form_filter', $form);
    }

    function headerSearch(Form $form, Request $request): Form
    {
        $form->text('keyword', ['placeholder' => trans('table.search.keyword').'...'], request()->input('keyword'));

        /**
         * @singe v7.0.0
         */
        return apply_filters('admin_products_feed_table_form_search', $form);
    }
}