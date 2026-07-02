$(document).ready(function () {
    $('.filtro_btn').click(function () {
        $('.filtro_btn').removeClass('active');
        $(this).addClass('active');

        var categoria = $(this).data('categoria');

        if (categoria == 'todos') {
            $('.card').show();
        } else {
            $('.card').hide();
            $('.card[data-type="' + categoria + '"]').show();
        }
    });
});