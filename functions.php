<?php
error_log('functions.php loaded');

require_once get_template_directory() . '/ajax-films-handler.php';

function register_film_post_type()
{
    register_post_type('films', [
        'labels' => [
            'name' => 'Фильмы',
            'singular_name' => 'Фильм',
            'add_new' => 'Добавить фильм',
            'add_new_item' => 'Добавить новый фильм',
            'edit_item' => 'Редактировать фильм',
            'new_item' => 'Новый фильм',
            'view_item' => 'Просмотреть фильм',
            'search_items' => 'Поиск фильмов',
            'not_found' => 'Фильмы не найдены',
            'not_found_in_trash' => 'В корзине фильмов не найдено',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'films'],
        'menu_position' => 5,
        'menu_icon' => 'dashicons-format-video',
        'supports' => [
            'title', 'editor', 'thumbnail', 'excerpt', 'author',
            'comments', 'revisions', 'custom-fields', 'taxonomies'
        ],
        'show_in_rest' => true,
    ]);
}

add_action('init', 'register_film_post_type');

function register_film_taxonomies()
{
    register_taxonomy('genre', 'films', [
        'labels' => [
            'name' => 'Жанры',
            'singular_name' => 'Жанр',
            'search_items' => 'Поиск жанров',
            'all_items' => 'Все жанры',
            'edit_item' => 'Редактировать жанр',
            'update_item' => 'Обновить жанр',
            'add_new_item' => 'Добавить новый жанр',
            'new_item_name' => 'Название нового жанра',
            'menu_name' => 'Жанры',
        ],
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'genre'],
    ]);

    register_taxonomy('country', 'films', [
        'labels' => [
            'name' => 'Страны',
            'singular_name' => 'Страна',
            'search_items' => 'Поиск стран',
            'all_items' => 'Все страны',
            'edit_item' => 'Редактировать страну',
            'update_item' => 'Обновить страну',
            'add_new_item' => 'Добавить новую страну',
            'new_item_name' => 'Название новой страны',
            'menu_name' => 'Страны',
        ],
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'country'],
    ]);
}

add_action('init', 'register_film_taxonomies');

function connect_taxonomies_to_films()
{
    register_taxonomy_for_object_type('genre', 'films');
    register_taxonomy_for_object_type('country', 'films');
}

add_action('init', 'connect_taxonomies_to_films', 20);

function add_film_meta_boxes()
{
    add_meta_box(
        'film_details',
        'Детали фильма',
        'render_film_meta_box',
        'films',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'add_film_meta_boxes');

function render_film_meta_box($post)
{
    wp_nonce_field('save_film_meta', 'film_meta_nonce');
    $price = get_post_meta($post->ID, '_film_price', true);
    $release_date = get_post_meta($post->ID, '_film_release_date', true);
    $selected_genres = wp_get_post_terms($post->ID, 'genre', ['fields' => 'ids']);
    $selected_countries = wp_get_post_terms($post->ID, 'country', ['fields' => 'ids']);
    $all_genres = get_terms(['taxonomy' => 'genre', 'hide_empty' => false]);
    $all_countries = get_terms(['taxonomy' => 'country', 'hide_empty' => false]);
    $image_id = get_post_meta($post->ID, '_film_custom_image_id', true);
    $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
    ?>
    <p>
        <label for="film_price">Стоимость (₽ или $):</label><br>
        <input type="text" name="film_price" id="film_price" value="<?php echo esc_attr($price); ?>"
               style="width:100%;">
    </p>
    <p>
        <label for="film_release_date">Дата выхода (YYYY-MM-DD):</label><br>
        <input type="date" name="film_release_date" id="film_release_date"
               value="<?php echo esc_attr($release_date); ?>" style="width:100%;">
    </p>
    <p>
        <label for="film_genre">Жанр:</label><br>
        <select name="film_genre" id="film_genre" style="width:100%;">
            <option value="">— Выберите жанр —</option>
            <?php foreach ($all_genres as $genre): ?>
                <option value="<?php echo esc_attr($genre->term_id); ?>" <?php selected(in_array($genre->term_id, $selected_genres)); ?>>
                    <?php echo esc_html($genre->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="film_country">Страна:</label><br>
        <select name="film_country" id="film_country" style="width:100%;">
            <option value="">— Выберите страну —</option>
            <?php foreach ($all_countries as $country): ?>
                <option value="<?php echo esc_attr($country->term_id); ?>" <?php selected(in_array($country->term_id, $selected_countries)); ?>>
                    <?php echo esc_html($country->name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="film_custom_image">Изображение фильма (кастомное):</label><br>
        <input type="hidden" name="film_custom_image_id" id="film_custom_image_id"
               value="<?php echo esc_attr($image_id); ?>"/>
        <img id="film_custom_image_preview" src="<?php echo esc_url($image_url); ?>"
             style="max-width: 200px; display: <?php echo $image_url ? 'block' : 'none'; ?>; margin-bottom:10px;"/>
        <br>
        <button type="button" class="button" id="upload_film_custom_image_button">
            <?php echo $image_url ? 'Изменить изображение' : 'Выбрать изображение'; ?>
        </button>
        <button type="button" class="button" id="remove_film_custom_image_button"
                style="display: <?php echo $image_url ? 'inline-block' : 'none'; ?>;">
            Удалить изображение
        </button>
    </p>
    <script>
        jQuery(document).ready(function ($) {
            var frame;
            $('#upload_film_custom_image_button').on('click', function (e) {
                e.preventDefault();
                if (frame) {
                    frame.open();
                    return;
                }
                frame = wp.media({
                    title: 'Выберите изображение фильма',
                    button: {text: 'Выбрать'},
                    multiple: false
                });
                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#film_custom_image_id').val(attachment.id);
                    $('#film_custom_image_preview').attr('src', attachment.url).show();
                    $('#remove_film_custom_image_button').show();
                    $('#upload_film_custom_image_button').text('Изменить изображение');
                });
                frame.open();
            });

            $('#remove_film_custom_image_button').on('click', function (e) {
                e.preventDefault();
                $('#film_custom_image_id').val('');
                $('#film_custom_image_preview').hide();
                $(this).hide();
                $('#upload_film_custom_image_button').text('Выбрать изображение');
            });
        });
    </script>
    <?php
}

function save_film_meta_fields($post_id)
{
    if (get_post_type($post_id) !== 'films') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['film_meta_nonce']) || !wp_verify_nonce($_POST['film_meta_nonce'], 'save_film_meta')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['film_price'])) {
        update_post_meta($post_id, '_film_price', sanitize_text_field($_POST['film_price']));
    }

    if (isset($_POST['film_release_date'])) {
        update_post_meta($post_id, '_film_release_date', sanitize_text_field($_POST['film_release_date']));
    }

    if (isset($_POST['film_genre']) && !empty($_POST['film_genre'])) {
        $genre_term_id = intval($_POST['film_genre']);
        wp_set_object_terms($post_id, [$genre_term_id], 'genre');
    } else {
        wp_set_object_terms($post_id, [], 'genre');
    }

    if (isset($_POST['film_country']) && !empty($_POST['film_country'])) {
        $country_term_id = intval($_POST['film_country']);
        wp_set_object_terms($post_id, [$country_term_id], 'country');
    } else {
        wp_set_object_terms($post_id, [], 'country');
    }

    if (isset($_POST['film_custom_image_id'])) {
        update_post_meta($post_id, '_film_custom_image_id', intval($_POST['film_custom_image_id']));
    }
}

add_action('save_post', 'save_film_meta_fields');

function enqueue_film_admin_scripts($hook)
{
    global $post_type;
    if (($hook == 'post-new.php' || $hook == 'post.php') && $post_type === 'films') {
        wp_enqueue_media();
        wp_enqueue_script('jquery');
    }
}

add_action('admin_enqueue_scripts', 'enqueue_film_admin_scripts');

function enqueue_film_filter_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script(
        'ajax-films-script',
        get_template_directory_uri() . '/js/ajax-films.js',
        array('jquery'),
        filemtime(get_template_directory() . '/js/ajax-films.js'),
        true
    );
    wp_localize_script(
        'ajax-films-script',
        'ajax_films',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('filter_films_nonce'),
        )
    );
}

add_action('wp_enqueue_scripts', 'enqueue_film_filter_scripts');

function my_films_theme_enqueue_styles()
{

    wp_enqueue_style(
        'films-filter-styles',
        get_template_directory_uri() . '/css/style.css',
        array(),
        filemtime(get_template_directory() . '/css/style.css')
    );
}

add_action('wp_enqueue_scripts', 'my_films_theme_enqueue_styles');


function my_add_film_to_cart_callback()
{
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error('WooCommerce или корзина недоступны.');
    }

    if (!isset($_POST['film_id']) || !is_numeric($_POST['film_id'])) {
        wp_send_json_error('Некорректный ID фильма.');
    }

    $film_post_id = intval($_POST['film_id']);


    $product_id = get_post_meta($film_post_id, '_linked_wc_product_id', true);


    if (!$product_id || get_post_type($product_id) !== 'product') {
        wp_send_json_error('Не удалось найти связанный продукт WooCommerce для этого фильма. Пожалуйста, сохраните фильм в админке, чтобы создать/обновить продукт.');
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Продукт WooCommerce не найден для данного фильма (ID: ' . $product_id . ').');
    }

    if (!$product->is_type('simple') || !$product->is_virtual()) {
        wp_send_json_error('Продукт фильма не является простым или виртуальным продуктом WooCommerce. Пожалуйста, проверьте настройки продукта в админке.');
    }

    $added_to_cart = WC()->cart->add_to_cart($product_id, 1);

    if ($added_to_cart) {
        wp_send_json_success('Фильм успешно добавлен в корзину.');
    } else {
        wp_send_json_error('Не удалось добавить фильм в корзину. Возможно, он уже в корзине или есть другие проблемы.');
    }
}


add_action('wp_ajax_add_film_to_cart', 'my_add_film_to_cart_callback');
add_action('wp_ajax_nopriv_add_film_to_cart', 'my_add_film_to_cart_callback');

function my_theme_enqueue_scripts()
{
    error_log('my_theme_enqueue_scripts is running');


    wp_enqueue_script(
        'films-cart-actions',
        get_template_directory_uri() . '/js/films-cart-actions.js',
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('films-cart-actions', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');


function link_film_to_woocommerce_product($post_id, $post, $update)
{

    if ($post->post_type !== 'films') {
        return;
    }


    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if (wp_is_post_autosave($post_id)) {
        return;
    }


    if (!current_user_can('edit_post', $post_id)) {
        return;
    }


    $film_price = get_post_meta($post_id, '_film_price', true);
    $film_title = get_the_title($post_id);
    $film_excerpt = get_the_excerpt($post_id);


    $wc_product_id = get_post_meta($post_id, '_linked_wc_product_id', true);

    $product_post = null;

    if ($wc_product_id && get_post_type($wc_product_id) === 'product') {

        $product_post = get_post($wc_product_id);
    }

    if (!$product_post) {

        $product_data = array(
            'post_title' => $film_title,
            'post_content' => $post->post_content,
            'post_excerpt' => $film_excerpt,
            'post_status' => 'publish',
            'post_type' => 'product',
            'comment_status' => 'closed',
        );

        $wc_product_id = wp_insert_post($product_data);

        if (is_wp_error($wc_product_id)) {
            error_log('Ошибка при создании продукта WooCommerce для фильма ' . $film_title . ': ' . $wc_product_id->get_error_message());
            return;
        }


        update_post_meta($post_id, '_linked_wc_product_id', $wc_product_id);

    } else {

        $product_data = array(
            'ID' => $wc_product_id,
            'post_title' => $film_title,
            'post_content' => $post->post_content,
            'post_excerpt' => $film_excerpt,
        );
        wp_update_post($product_data);
    }


    update_post_meta($wc_product_id, '_price', $film_price);
    update_post_meta($wc_product_id, '_regular_price', $film_price);
    update_post_meta($wc_product_id, '_virtual', 'yes');
    update_post_meta($wc_product_id, '_downloadable', 'no');
    update_post_meta($wc_product_id, '_stock_status', 'instock');
    update_post_meta($wc_product_id, '_manage_stock', 'no');
    update_post_meta($wc_product_id, '_sold_individually', 'no');


}

add_action('save_post', 'link_film_to_woocommerce_product', 10, 3);