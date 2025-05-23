jQuery(document).ready(function ($) {

    $(document).on('click', '.add-to-cart-film', function (e) {
        e.preventDefault();

        var $button = $(this);
        var filmId = $button.data('film-id');


        $button.prop('disabled', true).text('Добавляем...');

        $.ajax({
            url: myAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'add_film_to_cart',
                film_id: filmId,

            },
            success: function (response) {
                if (response.success) {
                    alert('Фильм успешно добавлен в корзину!');

                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                } else {
                    alert('Ошибка: ' + response.data);
                }

                $button.prop('disabled', false).text('Добавить в корзину');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                alert('Произошла ошибка при добавлении в корзину.');
                console.error("AJAX Error:", textStatus, errorThrown);

                $button.prop('disabled', false).text('Добавить в корзину');
            }
        });
    });
});