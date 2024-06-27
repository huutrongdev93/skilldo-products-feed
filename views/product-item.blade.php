<tr class="js_column js_product_item tr_{!! $item->id !!}" data-item="{!! htmlentities(json_encode($item)) !!}">
    <td class="check-column">
        <input class="icheck select" value="{!! $item->id !!}" type="checkbox" name="select[]" >
    </td>
    <td class="image column-image">
        <img src="{!! $item->image !!}" loading="lazy">
    </td>
    <td class="title column-title">
        <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{!! $item->id !!}" style="color:#000;">
            {!! $item->title !!}
        </span>
    </td>
    <td class="prices column-prices">
        <div class="product-variations-block">
            <div class="product-variations-model ">
                <p class="quick-edit-box d-flex gap-3">
                    <span class="product_price">{!! Prd::price($item->price) !!}</span>
                    <span class="product_price_sale">{!! Prd::price($item->price_sale) !!}</span>
                </p>
            </div>
        </div>
    </td>
</tr>