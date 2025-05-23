<?php
function handle_ajax_films()
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'filter_films_nonce')) {
        wp_die('Security check failed');
    }

    $title = sanitize_text_field($_POST['title'] ?? '');
    $price_from = isset($_POST['price_from']) && $_POST['price_from'] !== '' ? floatval($_POST['price_from']) : null;
    $price_to = isset($_POST['price_to']) && $_POST['price_to'] !== '' ? floatval($_POST['price_to']) : null;
    $genres = isset($_POST['genre']) && is_array($_POST['genre']) ? array_map('intval', $_POST['genre']) : [];
    $countries = isset($_POST['country']) && is_array($_POST['country']) ? array_map('intval', $_POST['country']) : [];
    $sort_by = sanitize_text_field($_POST['sort_by'] ?? '');
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $posts_per_page = 10;

    $args = [
        'post_type' => 'films',
        'posts_per_page' => $posts_per_page,
        'paged' => $paged,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    ];

    if (!empty($title)) {
        $args['s'] = $title;
    }

    $meta_query = [];

    if ($price_from !== null || $price_to !== null) {
        $price_filter = ['key' => '_film_price', 'type' => 'NUMERIC'];

        if ($price_from !== null && $price_to !== null) {
            $price_filter['value'] = [$price_from, $price_to];
            $price_filter['compare'] = 'BETWEEN';
        } elseif ($price_from !== null) {
            $price_filter['value'] = $price_from;
            $price_filter['compare'] = '>=';
        } elseif ($price_to !== null) {
            $price_filter['value'] = $price_to;
            $price_filter['compare'] = '<=';
        }
        $meta_query[] = $price_filter;
    }

    if (!empty($sort_by)) {
        switch ($sort_by) {
            case 'price_asc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_film_price';
                $args['order'] = 'ASC';
                break;
            case 'price_desc':
                $args['orderby'] = 'meta_value_num';
                $args['meta_key'] = '_film_price';
                $args['order'] = 'DESC';
                break;
            case 'date_asc':
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = '_film_release_date';
                $args['order'] = 'ASC';
                break;
            case 'date_desc':
                $args['orderby'] = 'meta_value';
                $args['meta_key'] = '_film_release_date';
                $args['order'] = 'DESC';
                break;
            default:
                break;
        }
    }

    if (count($meta_query) > 1) {
        $meta_query['relation'] = 'AND';
    }
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    $tax_query = [];

    if (!empty($genres)) {
        $tax_query[] = [
            'taxonomy' => 'genre',
            'field' => 'term_id',
            'terms' => $genres,
            'operator' => 'IN',
        ];
    }

    if (!empty($countries)) {
        $tax_query[] = [
            'taxonomy' => 'country',
            'field' => 'term_id',
            'terms' => $countries,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div class="films-list">';
        while ($query->have_posts()) {
            $query->the_post();
            $price = get_post_meta(get_the_ID(), '_film_price', true);
            $release_date = get_post_meta(get_the_ID(), '_film_release_date', true);
            $genres_terms = get_the_terms(get_the_ID(), 'genre');
            $countries_terms = get_the_terms(get_the_ID(), 'country');
            $custom_image_id = get_post_meta(get_the_ID(), '_film_custom_image_id', true);
            $custom_image_url = $custom_image_id ? wp_get_attachment_image_url($custom_image_id, 'medium') : '';

            echo '<div class="film-item" style="padding:10px; margin-bottom:10px; display: flex; gap: 15px; align-items: flex-start;">';
            if ($custom_image_url) {
                echo '<div class="film-custom-image"><img src="' . esc_url($custom_image_url) . '" alt="' . esc_attr(get_the_title()) . '" style="max-width: 200px;"></div>';
            } elseif (has_post_thumbnail()) {
                echo '<div class="film-thumbnail">';
                the_post_thumbnail('medium');
                echo '</div>';
            }
            echo '<div class="film-content">';
            echo '<h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
            echo '<div class="description">' . get_the_excerpt() . '</div>';
            echo '<p class="price"></strong> ' . esc_html($price ?: '—') . '$</p>';
            echo '<p><strong>Дата выхода:</strong> ' . esc_html($release_date ?: '—') . '</p>';
            echo '<p><strong>Жанр:</strong> ' . ($genres_terms ? implode(', ', wp_list_pluck($genres_terms, 'name')) : '—') . '</p>';
            echo '<p><strong>Страна:</strong> ' . ($countries_terms ? implode(', ', wp_list_pluck($countries_terms, 'name')) : '—') . '</p>';
            $film_post_id = get_the_ID();
            echo '<button class="add-to-cart-film Sort_btn" data-film-id="' . esc_attr($film_post_id) . '">Добавить в корзину</button>';
            echo '</div></div>';
        }
        echo '</div>';
    } else {
        echo '<p>Фильмы не найдены.</p>';
    }

    wp_reset_postdata();
    wp_die();
}

add_action('wp_ajax_filter_films', 'handle_ajax_films');
add_action('wp_ajax_nopriv_filter_films', 'handle_ajax_films');