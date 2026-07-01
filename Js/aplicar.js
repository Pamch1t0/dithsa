$(document).ready(function () {

    $("#modalAplicar").hide();

    $(document).on("click", ".apply", function () {
        let puesto = $(this).closest(".card").find("h3").text();
        $("#puestoVacante").val(puesto);
        $("#modalAplicar").fadeIn(300);
    });

    $(".cerrar").on("click", function () {
        $("#modalAplicar").fadeOut(300);
    });

    $(window).on("click", function (e) {
        if ($(e.target).is("#modalAplicar")) {
            $("#modalAplicar").fadeOut(300);
        }
    });

    $("#experiencia").on("change", function () {
        if ($(this).val() === "si") {
            $("#campoExperiencia").slideDown(200);
        } else {
            $("#campoExperiencia").slideUp(200);
        }
    });

});