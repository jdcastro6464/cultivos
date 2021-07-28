$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	$('#formNewClient').validate({
		rules: {
			nombre: 'required',
			direccion: 'required',
			idPersona: 'required',
			idDepartamento: 'required',
			idCiudad: 'required',
		},
		messages: {
			nombre: 'Por favor, ingrese el nombre completo',
			direccion: 'Por favor, ingrese el no. de documento',
			idPersona: 'Por favor, seleccione un Cliente',
			idDepartamento: 'Por favor, seleccione un departamento',
			idCiudad: 'Por favor, seleccione una ciudad',
		},
		errorElement: 'em',
		errorPlacement: function errorPlacement(error, element) {
			error.addClass('invalid-feedback');

			if (element.prop('type') === 'checkbox') {
				error.insertAfter(element.parent('label'));
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function highlight(element) {
			$(element).addClass('is-invalid').removeClass('is-valid');
		},
		unhighlight: function unhighlight(element) {
			$(element).addClass('is-valid').removeClass('is-invalid');
		},
		submitHandler: function submitHandler() {
			
			$.ajax({
				type: "POST",
				url: "../../controllers/controllersAdministrador.php",
				data: $("#formNewClient").serialize()+"&peticion=agregarCliente",
				dataType: "json",
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Espere...',0);
					$("#btnAdd").attr('disabled', 'disabled');
				}
			}).done(function( data ){
				alertify.dismissAll();
				$("#btnAdd").removeAttr('disabled');
				if (data.exito) {
					tabla.ajax.reload();
					$("#newClientModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formNewClient")[0].reset();
					alertas_s(data.msj);
				} else {
					alertas_e(data.msj);
				}
			});

		}
	});

	$("#idDepartamento").on("change", function(){
		let idDepart = $(this).val();

		if (idDepart != "") {
			$.ajax({
				type: "POST",
				url: "../../controllers/controllersAdministrador.php",
				data: {
					peticion: 'consultaCuidad',
					idDepart: idDepart
				},
				dataType: "json",
				beforeSend:function(xhr){
					
				}
			}).done(function( data ){
				if (data.exito) {
					$("#idCiudad").empty();
					$("#idCiudad").html(data.html);
				} else {
					alertas_e(data.msj);
				}
			});
		} else {
			$("#idCiudad").empty();
			$("#idCiudad").html(`<option value="" selected>Seleccione una Ciudad</option>`);
		}
	});

	$("#edit-idDepartamento").on("change", function(){
		let idDepart = $(this).val();

		if (idDepart != "") {
			$.ajax({
				type: "POST",
				url: "../../controllers/controllersAdministrador.php",
				data: {
					peticion: 'consultaCuidad',
					idDepart: idDepart
				},
				dataType: "json",
				beforeSend:function(xhr){
					
				}
			}).done(function( data ){
				if (data.exito) {
					$("#edit-idCiudad").empty();
					$("#edit-idCiudad").html(data.html);
				} else {
					alertas_e(data.msj);
				}
			});
		} else {
			$("#edit-idCiudad").empty();
			$("#edit-idCiudad").html(`<option value="" selected>Seleccione una Ciudad</option>`);
		}
	});

	let tabla;

	function tabla_clientes(){
		tabla = $('#table_clientes').DataTable({
			order : [],
			select: true,
			searching: true,
			bDeferRender: true,     
			sPaginationType: "full_numbers",
			ajax: {
				url: "../../controllers/controllersAdministrador.php",
				type: "POST",
				data: {
					peticion : "listarClientes"
				},
				dataSrc: function (json) { 
					let datos = json.data;
					if ( datos.length > 0 ) {
						$("#msj").css('display', 'none');
						$("#msj").empty();

						setTimeout(() => {
							$("#aprobacion").css('display', 'block');
						},500);

						return json.data;
					}else{
						$("#aprobacion").css('display', 'none');

						setTimeout(() => {
							$("#msj").html("<h2>¡No hay clientes almacenados!</h2>");
							$("#msj").css('display', 'block');
						},500);
					} 
				}
			},      
			columns: [
			{ "data": "Opciones" },
			{ "data": "Cliente" },
			{ "data": "Nombre" },
			{ "data": "Dirección" },
			{ "data": "Departamento" },
			{ "data": "Ciudad" },
			],
			oLanguage: {
				"sProcessing": "Procesando...",
				"sLengthMenu": 'Mostrar <select>'+
				'<option value="10">10</option>'+
				'<option value="20">20</option>'+
				'<option value="30">30</option>'+
				'<option value="40">40</option>'+
				'<option value="50">50</option>'+
				'<option value="-1">All</option>'+
				'</select> registros',    
				"sZeroRecords":    "No se encontraron resultados",
				"sEmptyTable":     "Ningún dato disponible en esta tabla",
				"sInfo":           "Mostrando del (_START_ al _END_) de un total de _TOTAL_ registros",
				"sInfoEmpty":      "Mostrando del 0 al 0 de un total de 0 registros",
				"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
				"sInfoPostFix":    "",
				"sSearch":         "Filtrar:",
				"sUrl":            "",
				"sInfoThousands":  ",",
				"sLoadingRecords": "Por favor espere - cargando...",
				"oPaginate": {
					"sFirst":    "Primero",
					"sLast":     "Último",
					"sNext":     "Siguiente",
					"sPrevious": "Anterior" 
				},
				"oAria": {
					"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
					"sSortDescending": ": Activar para ordenar la columna de manera descendente"
				}
			}
		});

		tabla.on('draw',function ( e, settings, details ){
			$(function(){
        //Tooltip
        $('[data-toggle="tooltip"]').tooltip({
        	trigger : 'hover'
        });
        
        $('[data-toggle="tooltip"]').on('click', function () {
        	$(this).tooltip('hide')
        });
      });
			eliminarEntidad();
			editarEntidad();
		});
	}

	$(function(){
		let archivo = "cliente.php";
		let pathname = window.location.pathname;
		let array = pathname.split("/");
		let archivo2 = array.pop ();

		if(archivo == archivo2){
			if ( $.fn.dataTable.isDataTable('#table_clientes') ) {
				tabla.destroy();
				tabla_clientes();
			}
			else {
				tabla_clientes();
			}
		}
	});

	function editarEntidad() {
		$("#table_clientes").find(".editar").each(function() {
			$(this).unbind('click');
			$(this).click(function(){
				$("#btnEdit").attr({
					"data-idEnti": $(this).attr("data-idEnti")
				});

				$.ajax({
					type: "POST",
					url: "../../controllers/controllersAdministrador.php",
					data: {
						peticion: 'traerInformacionEntidad',
						idEnti: $(this).attr("data-idEnti")
					},
					dataType: "json",
					beforeSend:function(xhr){
						
					}
				}).done(function( data ){
					if (data.exito) {
						$("#edit-idCiudad").empty();
						$("#edit-idCiudad").html(data.html);

						$("#edit-nombre").val(data.nombreLugar);
						$("#edit-direccion").val(data.direccion);
						$("#edit-idPersona").val(data.idPersona);
						$("#edit-idDepartamento").val(data.idDepartamento);
						$("#edit-idCiudad").val(data.idCiudad);

						$("#editClientModal").modal('show');
					} else {
						alertas_e(data.msj);
					}
				});
			})
		})
	}

	$("#editClientModal").on('hidden.bs.modal', function() {
		$("#formEditClient")[0].reset();
		$("#btnEdit").removeAttr('data-idEnti');
	});

	$('#formEditClient').validate({
		rules: {
			"edit-nombre": 'required',
			"edit-direccion": 'required',
			"edit-idPersona": 'required',
			"edit-idDepartamento": 'required',
			"edit-idCiudad": 'required',
		},
		messages: {
			"edit-nombre": 'Por favor, ingrese el nombre completo',
			"edit-direccion": 'Por favor, ingrese el no. de documento',
			"edit-idPersona": 'Por favor, seleccione un Cliente',
			"edit-idDepartamento": 'Por favor, seleccione un departamento',
			"edit-idCiudad": 'Por favor, seleccione una ciudad',
		},
		errorElement: 'em',
		errorPlacement: function errorPlacement(error, element) {
			error.addClass('invalid-feedback');

			if (element.prop('type') === 'checkbox') {
				error.insertAfter(element.parent('label'));
			} else {
				error.insertAfter(element);
			}
		},
		highlight: function highlight(element) {
			$(element).addClass('is-invalid').removeClass('is-valid');
		},
		unhighlight: function unhighlight(element) {
			$(element).addClass('is-valid').removeClass('is-invalid');
		},
		submitHandler: function submitHandler() {
			let idEnti = $("#btnEdit").attr('data-idEnti');

			$.ajax({
				type: "POST",
				url: "../../controllers/controllersAdministrador.php",
				data: $("#formEditClient").serialize()+`&idEnti=${idEnti}&peticion=actualizarEntidad`,
				dataType: "json",
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Espere...',0);
					$("#btnEdit").attr('disabled', 'disabled');
				}
			}).done(function( data ){
				alertify.dismissAll();
				$("#btnEdit").removeAttr('disabled');
				if (data.exito) {
					tabla.ajax.reload();
					$("#editClientModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formEditClient")[0].reset();
					alertas_s(data.msj);
				} else {
					alertas_e(data.msj);
				}
			});

		}
	});

	let eliminarSi = {
		"funcion" : function(){
			$.ajax({
				type: 'POST',
				url: '../../controllers/controllersAdministrador.php',
				data: {
					peticion : 'eliminarEntidad',
					idEnti : eliminarSi['idEnti'],
				},
				dataType: 'JSON'
			}).done(function( data ){
				if ( data['exito'] ) {
					tabla.ajax.reload();
					alertas_s( data['msj'] );
				}else{
					alertas_e( data['msj'] );
				}
			});
		},
		"idEnti" : ""
	}

	function eliminarEntidad(){
		$("#table_clientes").find(".eliminar").each(function(){

			$(this).unbind("click");
			$(this).click(function(){
				eliminarSi['idEnti'] = $(this).attr("data-idEnti");
				deleteds( "Eliminar Entidad", "<h6><i class=\"fas fa-check\"></i> Seguro que desea eliminar la Entidad</h6><h6><i class=\"fas fa-check\"></i> Si elimina la Entidad no podrá recuperarlo nuevamente</h6>", eliminarSi['funcion'] , null );
			});
		});
	};

});