<?php
/**
 * WooCommerce Product Importer for UrbanFit BD Assessment
 */

require_once __DIR__ . '/wp-load.php';

if (!class_exists('WooCommerce')) {
    die("WooCommerce is not active.\n");
}

$json_file = __DIR__ . '/Sample Product Data.json';
if (!file_exists($json_file)) {
    die("Sample Product Data.json not found.\n");
}

$json_content = file_get_contents($json_file);
$products_data = json_decode($json_content, true);

if (!$products_data) {
    die("Failed to decode JSON data.\n");
}

echo "Starting Product Import for UrbanFit BD...\n";

// 1. Setup Global Attribute 'pa_size'
$attribute_name = 'Size';
$attribute_slug = 'size';
$attribute_id = wc_attribute_taxonomy_id_by_name($attribute_slug);

if (!$attribute_id) {
    $attribute_id = wc_create_attribute(array(
        'name'         => $attribute_name,
        'slug'         => $attribute_slug,
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => false,
    ));
}

// Ensure size taxonomy terms exist
$all_possible_sizes = array('S', 'M', 'L', 'XL', 'One Size');
foreach ($all_possible_sizes as $size_term) {
    if (!term_exists($size_term, 'pa_size')) {
        wp_insert_term($size_term, 'pa_size');
    }
}

foreach ($products_data as $data) {
    $name        = $data['name'];
    $category    = $data['category'];
    $price       = $data['price'];
    $sku         = $data['sku'];
    $stock       = intval($data['stock']);
    $sizes       = $data['sizes'];
    $description = $data['description'];
    $image_url   = $data['image'];

    echo "Processing: $name ($sku)... ";

    // Check if product already exists by SKU
    $existing_id = wc_get_product_id_by_sku($sku);
    if ($existing_id) {
        wp_delete_post($existing_id, true);
    }

    // Create Category if not exists
    $term = term_exists($category, 'product_cat');
    if (!$term) {
        $term = wp_insert_term($category, 'product_cat');
    }
    $cat_id = is_array($term) ? $term['term_id'] : $term;

    // Create Variable Product
    $product = new WC_Product_Variable();
    $product->set_name($name);
    $product->set_description($description);
    $product->set_short_description($description);
    $product->set_sku($sku);
    $product->set_category_ids(array($cat_id));
    $product->set_manage_stock(true);
    $product->set_stock_quantity($stock);
    $product->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
    $product->set_regular_price($price);
    $product->set_price($price);

    // Set Size Attribute
    $attribute = new WC_Product_Attribute();
    $attribute->set_id(wc_attribute_taxonomy_id_by_name('size'));
    $attribute->set_name('pa_size');
    $attribute->set_options($sizes);
    $attribute->set_position(0);
    $attribute->set_visible(true);
    $attribute->set_variation(true);

    $product->set_attributes(array('pa_size' => $attribute));
    $product_id = $product->save();

    // Set Terms for pa_size
    wp_set_object_terms($product_id, $sizes, 'pa_size');

    // Create Variations for each size
    foreach ($sizes as $size) {
        $variation = new WC_Product_Variation();
        $variation->set_parent_id($product_id);
        $variation->set_attributes(array('pa_size' => sanitize_title($size)));
        $variation->set_regular_price($price);
        $variation->set_price($price);
        $variation->set_sku($sku . '-' . sanitize_title($size));
        $variation->set_manage_stock(true);
        $variation->set_stock_quantity($stock);
        $variation->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
        $variation->save();
    }

    // Save featured image placeholder / URL meta
    update_post_meta($product_id, '_external_image_url', $image_url);

    echo "Created Product ID: $product_id (Stock: $stock)\n";
}

echo "All 8 Products Imported Successfully!\n";
