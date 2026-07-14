$(document).ready(function() {
    $('.btn-toggle').click(function() {
        const esVacantes = $(this).index('.btn-toggle') === 0;

        $('#table-vacantes').toggleClass('active', esVacantes);
        $('#table-contactos').toggleClass('active', !esVacantes);

        $('.btn-toggle').removeClass('active');
        $(this).addClass('active');

        $('#vistaOculta').val(esVacantes ? 'vacantes' : 'contactos');
    });
});