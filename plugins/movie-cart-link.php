<?php
/**
 * Plugin Name: Movie to WooCommerce Link
 * Description: Привязка фильмов к WooCommerce продуктам
 */

add_action('add_meta_boxes', function () {
    add_meta_box(
        'movie_product_link',
        'Привязанный товар WooCommerce',
        function ($post) {
            $product_id = get_post_meta($post->ID, '_linked_product_id', true);
            ?>
            <label for="linked_product_id">ID товара:</label>
            <input type="number" name="linked_product_id" value="<?php echo esc_attr($product_id); ?>"
                   style="width: 100%;"/>
            <?php
        },
        'films',
        'side',
        'default'
    );
});

add_action('save_post_films', function ($post_id) {
    if (isset($_POST['linked_product_id'])) {
        update_post_meta($post_id, '_linked_product_id', intval($_POST['linked_product_id']));
    }
});