document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formContacto');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const boton = form.querySelector('button[type="submit"]');
        const textoOriginal = boton.textContent;
        boton.disabled = true;
        boton.textContent = 'Enviando...';

        const formData = new FormData(form);

        fetch('../Backend/controllers/enviarContacto.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Mensaje enviado!',
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