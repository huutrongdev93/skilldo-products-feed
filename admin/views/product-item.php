<tr class="js_column js_product_item tr_<?php echo $item->id;?>" data-item="<?php echo htmlentities(json_encode($item));?>">
    <td class="check-column">
        <input class="icheck select" value="<?php echo $item->id;?>" type="checkbox" name="select[]" >
    </td>
    <td class="image column-image">
        <img src="<?php echo $item->image;?>" loading="lazy">
    </td>
    <td class="title column-title">
        <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $item->id;?>" style="color:#000;">
            <?php echo $item->title;?>
        </span>
    </td>
    <td class="prices column-prices">
        <div class="product-variations-block">
            <div class="product-variations-model ">
                <p class="quick-edit-box d-flex gap-3">
                    <span class="product_price"><?php echo number_format($item->price);?></span>
                    <span class="product_price_sale"><?php echo number_format($item->price_sale);?></span>
                </p>
            </div>
        </div>
    </td>
</tr>