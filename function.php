<?php
Class PrFeed extends \SkillDo\Model\Model{

    protected string $table = 'products_feed';

    protected array $columns = [
        'value'             => ['array'],
    ];
}

Class PFeedHelper {
    static function categoryGoogle() {
        $category = \SkillDo\Cache::get('GoogleProductsFeedCategory');
        if(empty($category)) {
            $category = [];
            $handle = fopen("https://www.google.com/basepages/producttype/taxonomy.vi-VN.txt", "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (str_starts_with($line, '#')) continue;
                    $line = html_escape(trim($line));
                    $category[$line] = $line;
                }
                fclose($handle);
                \SkillDo\Cache::save('ProductsFeedCategory', $category);
            }
        }
        return $category;
    }
    static function categoryFacebook() {
        $category = \SkillDo\Cache::get('FacebookProductsFeedCategory');
        if(empty($category)) {
            $category = [];
            $handle = fopen(Path::plugin(PR_FEED_NAME).'/categories/facebook.txt', "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (str_starts_with($line, '#')) continue;
                    $line = html_escape(trim($line));
                    $category[$line] = $line;
                }
                fclose($handle);
                \SkillDo\Cache::save('FacebookProductsFeedCategory', $category);
            }
        }
        return $category;
    }
    static function config($key = '') {

        $setting = [
            'categoriesGoogle'      => [],
            'categoriesFacebook'    => [],
            'id'                    => 'id',
            'availability'          => 'in_stock',
            'condition'             => 'new',
            'size'                  => '',
            'color'                 => '',
        ];

        $option = Option::get('ProductsFeedConfig', $setting);

        if(!have_posts($option)) {
            $option = $setting;
        }
        else {
            $option = array_merge($setting, $option);
        }

        if(have_posts($option['categoriesGoogle'])) {
            $categories = [];
            foreach ($option['categoriesGoogle'] as $category) {
                $categories[$category] = $category;
            }
            $option['categoriesGoogle'] = $categories;
        }

        if(have_posts($option['categoriesFacebook'])) {
            $categories = [];
            foreach ($option['categoriesFacebook'] as $category) {
                $categories[$category] = $category;
            }
            $option['categoriesFacebook'] = $categories;
        }

        if(!empty($key)) {
            return Arr::get($option, $key);
        }

        return $option;
    }
    static function availability(): array
    {
        $options = [
            'system'        => 'Lấy từ tình trạng kho hàng website',
            'in_stock'      => 'Còn hàng',
            'out_of_stock'  => 'Hết hàng',
            'preorder'      => 'Đặt hàng trước',
            'backorder'     => 'Giao sau',
        ];
        if(!class_exists('stock_manager')) {
            unset($options['system']);
        }
        return $options;
    }

    /**
     * @throws DOMException
     */
    static function createdXML($feed): bool|string
    {
        $xml = '';

        if(have_posts($feed)) {

            $args = Qr::set('public', 1)->where('trash', 0)->orderByDesc('created');

            if($feed->type == 'category') {
                $args = Qr::set('public',1)->where('trash', 0)->orderBy('order')->orderBy('created', 'desc');
                $args->whereByCategory($feed->categoryWebsite);
            }

            if($feed->type == 'productsCustom') {
                $feed->value = unserialize($feed->value);
                $args = Qr::set('public',1)->where('trash', 0)->whereIn('id', $feed->value)->orderBy('order')->orderBy('created', 'desc');
            }

            $products = Product::gets($args);

            $setting = PFeedHelper::config();

            $feedXml = [];

            foreach ($products as $product) {

                $productFeed = Product::getMeta($product->id, 'productsFeed', true);

                if(!empty($productFeed['availability'])) $setting['availability'] = $productFeed['availability'];

                if(!empty($productFeed['condition'])) $setting['condition'] = $productFeed['condition'];

                if(!empty($productFeed['size'])) $setting['size'] = $productFeed['size'];

                if(!empty($productFeed['color'])) $setting['color'] = $productFeed['color'];

                $gf_product = [];
                //FLAGS FOR LATER
                $gf_product['is_on_sale'] = $product->price_sale != 0; //set True or False depending on whether product is on sale

                //Mã nhận dạng duy nhất của sản phẩm
                $gf_product['g:id']                         = (!empty($setting['id']) && $setting['id'] == 'code') ? $product->code : $product->id;

                //Tên của sản phẩm
                $gf_product['g:title']                      = $product->title;

                //Nội dung mô tả của sản phẩm
                $gf_product['g:description']                = Str::clear($product->seo_description);

                //Trang đích của sản phẩm
                $gf_product['g:link']                       = Url::base($product->slug);

                //URL hình ảnh chính của sản phẩm
                $gf_product['g:image_link']                 = Url::base().Template::imgLink($product->image);

                //URL của hình ảnh bổ sung dành cho sản phẩm
                $gallery = GalleryItem::gets(Qr::set('object_id', $product->id)->where('object_type', 'products'));

                if(have_posts($gallery)) {
                    $gf_product['g:additional_image_link'] = '';
                    foreach ($gallery as $item) {
                        if($item->type != 'image') continue;
                        $gf_product['g:additional_image_link'] .= Url::base().Template::imgLink($item->value).',';
                    }
                    if(!empty($gf_product['g:additional_image_link'])) {
                        $gf_product['g:additional_image_link'] = trim($gf_product['g:additional_image_link'], ',');
                    }
                    else {
                        unset($gf_product['g:additional_image_link']);
                    }
                }

                //Tình trạng còn hàng của sản phẩm
                if($setting['availability'] == 'system') {
                    if($product->stock_status == 'outstock') {
                        $gf_product['g:availability'] = 'out_of_stock';
                    }
                    else $gf_product['g:availability'] = 'in_stock';
                }
                else {
                    $gf_product['g:availability'] = $setting['availability'];
                }

                //Giá của sản phẩm
                $gf_product['g:price']        = $product->price.' VND';

                //Giá ưu đãi của sản phẩm
                if (!empty($product->price_sale)) {
                    $gf_product['g:sale_price'] = $product->price_sale. ' VND';
                }

                //Danh mục sản phẩm do Google xác định cho sản phẩm của bạn
                $gf_product['g:google_product_category']  = (empty($productFeed['category'])) ? html_escape($feed->categoryGoogle) : $productFeed['category'];
                $gf_product['g:fb_product_category']  = (empty($productFeed['category'])) ? html_escape($feed->categoryGoogle) : $productFeed['category'];


                //Tên thương hiệu của sản phẩm
                if(!empty($product->brand_id)) {
                    $brand = Brand::get($product->brand_id);
                    if(have_posts($brand)) {
                        $gf_product['g:brand'] = $brand->name;
                    }
                }

                if($product->hasVariation == 1) {
                    $attributes = Attributes::gets(['product_id' => $product->id]);
                    if(!empty($attributes)) {
                        foreach ($attributes as $attr) {
                            if($setting['size'] == $attr['id'] || $setting['color'] == $attr['id']) {
                                $attrLabel = [];
                                foreach ($attr['items'] as $item) {
                                    $attrLabel[] = $item->title;
                                }
                                if(!empty($attr)) {
                                    if($setting['color'] == $attr['id']) $gf_product['g:color'] = implode('/', $attrLabel);
                                    if($setting['size'] == $attr['id']) $gf_product['g:color'] = implode('/', $attrLabel);
                                }
                            }
                        }
                    }
                }

                if(!empty($productFeed['gender'])) {
                    $gf_product['g:gender'] = $productFeed['gender'];
                }

                //Tình trạng của sản phẩm tại thời điểm bán
                $gf_product['g:condition']  = (!empty($setting['condition'])) ? $setting['condition'] : 'new';

                //Cho biết sản phẩm có chứa nội dung khiêu dâm
                $gf_product['g:adult'] = 'no';

                $gf_product['g:identifier_exists'] = 'no';

                //Thông số kỹ thuật hoặc chi tiết bổ sung của sản phẩm
                if(!empty($productGoogle['productDetail'])) {
                    $gf_product['g:product_detail'] = $productGoogle['productDetail'];
                }
                //Những đặc điểm nổi bật phù hợp nhất của sản phẩm
                if(!empty($productGoogle['productHighlight'])) {
                    $gf_product['g:product_highlight'] = $productGoogle['productHighlight'];
                }

                $feedXml[] = $gf_product;
            }

            $doc = new DOMDocument('1.0', 'UTF-8');

            $xmlRoot = $doc->createElement("rss");

            $xmlRoot = $doc->appendChild($xmlRoot);

            $xmlRoot->setAttribute('version', '2.0');

            $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:g', "http://base.google.com/ns/1.0");

            $channelNode = $xmlRoot->appendChild($doc->createElement('channel'));

            if(!empty(Option::get('general_label'))) {
                $channelNode->appendChild($doc->createElement('title', Option::get('general_label')));
            }

            $channelNode->appendChild($doc->createElement('link', Url::base()));

            foreach ($feedXml as $product) {

                $itemNode = $channelNode->appendChild($doc->createElement('item'));

                foreach ($product as $keyPr => $item) {

                    if(empty($item)) continue;

                    if($keyPr == 'g:additional_image_link') {
                        $item = explode($item, ',');

                        foreach ($item as $img) {
                            $itemNode->appendChild($doc->createElement($keyPr, $img));
                        }
                    }
                    else {
                        $item = str_replace('&', 'và', $item);
                        $itemNode->appendChild($doc->createElement($keyPr, $item));
                    }
                }
            }

            $doc->formatOutput = true;

            $xml = $doc->saveXML();

            \SkillDo\Cache::save('productsFeed_'.$feed->key, $xml, 12*60*60);

            PrFeed::where('id', $feed->id)->update(['timeUp' => time()]);
        }

        return $xml;
    }
}

function ProductsFeedXml(): void
{
    //https://support.google.com/merchants/answer/7052112?visit_id=637456725860561063-1307432151&rd=1

    header('Content-type: application/xml');

    $key = Request::get('sign');

    $cacheKey = 'productsFeed_'.$key;

    $xml = \SkillDo\Cache::get($cacheKey);

    if(empty($xml)) {
        $feed = PrFeed::get(Qr::set('key' , $key));
        $xml = PFeedHelper::createdXML($feed);
    }

    echo $xml;
}