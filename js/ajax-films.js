jQuery(document).ready(function ($) {

    function fetchFilms() {

        var formData = $('#films-filter-form').serialize();

        $.ajax({
            url: ajax_films.ajax_url,
            type: 'POST',

            data: formData + '&action=filter_films&nonce=' + ajax_films.nonce,


            beforeSend: function () {
                $('#films-results').html('<p>Загрузка...</p>');
            },


            success: function (response) {
                $('#films-results').html(response);
            },


            error: function (jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error: ", textStatus, errorThrown, jqXHR.responseText);
                $('#films-results').html('<p>Произошла ошибка при загрузке фильмов.</p>');
            }
        });
    }


    $('#films-filter-form').on('submit', function (e) {
        e.preventDefault();
        fetchFilms();
    });


    $('#sort_by').on('change', function () {
        fetchFilms();
    });


    $('#reset-filters').on('click', function () {
        $('#films-filter-form')[0].reset();
        fetchFilms();
    });

    fetchFilms();
});