<?php
/* Template Name: Films Page */
get_header();
?>

<h3 class="Page_title">Каталог фильмов от Романа</h3>

    <form id="films-filter-form">
        <input type="text" name="title" placeholder="Название">
        <input type="number" name="price_from" placeholder="Цена от" min="0">
        <input type="number" name="price_to" placeholder="Цена до" min="0">
        <div class="types_row">
            <div class="types">
                <strong>Жанры:</strong><br>
                <?php foreach (get_terms(['taxonomy' => 'genre', 'hide_empty' => false]) as $term): ?>
                    <label class="types_label">
                        <input type="checkbox" name="genre[]" value="<?php echo esc_attr($term->term_id); ?>">
                        <?php echo esc_html($term->name); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <div class="types">
                <strong>Страны:</strong><br>
                <?php foreach (get_terms(['taxonomy' => 'country', 'hide_empty' => false]) as $term): ?>
                    <label class="types_label">
                        <input type="checkbox" name="country[]" value="<?php echo esc_attr($term->term_id); ?>">
                        <?php echo esc_html($term->name); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="Last_btns">
            <button class="Sort_btn" type="submit" style="margin-top: 15px;">Применить фильтр</button>
            <button class="Sort_btn" type="button" id="reset-filters" style="margin-top: 15px;">Сбросить фильтры
            </button>
            <div class="Sort_drop">
                <strong>Сортировка:</strong><br>
                <select class="Sort" name="sort_by" id="sort_by">
                    <option value="">— Выберите сортировку —</option>
                    <option value="price_asc">По стоимости (возрастание)</option>
                    <option value="price_desc">По стоимости (убывание)</option>
                    <option value="date_asc">По дате выхода (возрастание)</option>
                    <option value="date_desc">По дате выхода (убывание)</option>
                </select>
            </div>
        </div>
    </form>

    <div id="films-results">
        <p>Загрузка фильмов...</p>
    </div>

<?php get_footer(); ?>