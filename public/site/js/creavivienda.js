$(document).ready(function () {
    $(".button-winona").on("click", function () {
        var categorias = {};
        var i = 0;

        $('#tabla-categorias td[id]').each(function () {
            var id = $(this).attr('id');
            categorias[i] = id;
            i++;
        });

        console.log(categorias);

        var titulo = $('#contact-first-name').val();
        var npersonas = $('#contct-number').val();
        var descripcion = $('#contact-message').val();
        var localidad_id = $('#LocalidadesSelect').val();
        var usuario_id = $('#idusuario').text().trim();
        var latitud = $('#latitudIni').text();
        var longitud = $('#longitudIni').text();

        var punto_inicio = {
            latitud: latitud,
            longitud: longitud
        };

        var datosvivienda = {
            titulo: titulo,
            npersonas: npersonas,
            descripcion: descripcion,
            punto_inicio: punto_inicio,
            localidad_id: localidad_id,
            usuario_id: usuario_id
        };

        console.log(datosvivienda);

        var numeroFilas = $("#tablaprog tbody tr").length;
        var jsonArrayProgramacion = [];

        for (var j = 0; j < numeroFilas; j++) {
            var filaActual = $("#tablaprog tbody tr").eq(j);
            var diassemanaSeleccionada = filaActual.find("td.diasSemana").text();
            var arrayDiasSemana = diassemanaSeleccionada;
            var precio = filaActual.find("td.precio").text();
            var tiniSeleccionada = filaActual.find("td.temporadaIni").text();
            var tfinSeleccionada = filaActual.find("td.temporadaFin").text();

            var jsonData = {
                diasSemana: arrayDiasSemana,
                precio: precio,
                temporadaIni: tiniSeleccionada,
                temporadaFin: tfinSeleccionada
            };

            jsonArrayProgramacion.push(jsonData);
        }

        console.log(jsonArrayProgramacion);

        $.ajax({
            url: '/vivienda/crear',
            type: 'POST',
            data: JSON.stringify(datosvivienda),
            contentType: 'application/json; charset=utf-8',
            success: function (response) {
                alert('vivienda: ' + response.message);
                viviendaId = response.message;
alert("viviendaId")
                fetch('/viviendadisponibilidad/crear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ vivienda_id: viviendaId, jsonArrayProgramacion: jsonArrayProgramacion })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Success:', data);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });

                var cantidadFotos = $(".uploaded-image").length;

                for (var i = 0; i < cantidadFotos; i++) {
                    var foto = $("input[type=file]")[0].files[i];
                    var formdata = new FormData();
                    formdata.append("foto", foto, foto.name);
                    formdata.append("vivienda_id", viviendaId);

                    $.ajax({
                        url: '/ViviendaFoto/crear',
                        type: 'POST',
                        data: formdata,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            console.log('Respuesta del servidor:', data);
                            alert("foto creada");

                            var categoriasArray = [];
                            $('#tabla-categorias td[id]').each(function () {
                                var id = $(this).attr('id');
                                var categoria = {
                                    id: id,
                                    viviendaId: viviendaId
                                };
                                categoriasArray.push(categoria);
                            });

                            console.log(categoriasArray);

                            fetch('/viviendacategorias/crear', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(categoriasArray)
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Respuesta de la API:', data);
                            })
                            .catch(error => {
                                console.error('Error al hacer la solicitud:', error);
                            });
                        },
                        error: function (xhr, status, error) {
                            var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Error en el servidor';
                            console.error('Error en la solicitud:', error);
                            alert("Error al crear la foto: " + errorMessage);
                        }
                    });
                }
            },
            error: function (error) {
                alert('Error al enviar los datos: ' + error);
            }
        });
    });
});
