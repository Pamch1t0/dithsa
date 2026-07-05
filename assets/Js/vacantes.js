document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formAplicacion');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const boton = form.querySelector('button[type="submit"]');
        const textoOriginal = boton.textContent;
        boton.disabled = true;
        boton.textContent = 'Enviando...';

        const formData = new FormData(form);

        fetch('../Backend/controllers/enviar.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalAplicar').style.display = 'none';

                Swal.fire({
                    icon: 'success',
                    title: '¡Se envio correctamente la solicitud!',
                    text: data.message,
                    confirmButtonColor: '#0d6efd' 
                });
                form.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Ups...',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo contactar con el servidor. Intenta más tarde.'
            });
        })
        .finally(() => {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        });
    });
});