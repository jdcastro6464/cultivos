$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	$('#formNewUser').validate({
		rules: {
			nombreC: 'required',
			documento: 'required',
			email: {
				required: true,
				email: true
			},
			celular: 'required',
			usuario: {
				required: true,
				minlength: 10
			},
			password: {
				required: true,
				minlength: 10
			},
			passwordC: {
				required: true,
				minlength: 10,
				equalTo: '#password'
			},
			idRol: 'required',
		},
		messages: {
			nombreC: 'Por favor, ingrese el nombre completo',
			documento: 'Por favor, ingrese el no. de documento',
			email: {
				required: 'Por favor, ingrese el E-mail',
				email: 'E-mail inválido'
			},
			celular: 'Por favor, ingrese el no. de celular',
			usuario: {
				required: 'Por favor, ingrese el usuario',
				minlength: 'Mínimo 10 caracteres'
			},
			password: {
				required: 'Por favor, ingrese la contraseña',
				minlength: 'Mínimo 10 caracteres'
			},
			passwordC: {
				required: 'Por favor, ingrese la confirmación de la contraseña',
				minlength: 'Mínimo 10 caracteres',
				equalTo: 'Las contraseñas no coinciden'
			},
			idRol: 'Por favor, seleccione un rol',
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
				data: $("#formNewUser").serialize()+"&peticion=agregarUsuario",
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
					$("#newUserModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formNewUser")[0].reset();
					alertas_s(data.msj);
				} else {
					alertas_e(data.msj);
				}
			});

		}
	});

	let tabla;

	function tabla_usuarios(){
		tabla = $('#table_usuarios').DataTable({
			order : [],
			select: true,
			searching: true,
			bDeferRender: true,     
			sPaginationType: "full_numbers",
			ajax: {
				url: "../../controllers/controllersAdministrador.php",
				type: "POST",
				data: {
					peticion : "listarUsuarios"
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
							$("#msj").html("<h2>¡No hay usuarios almacenados!</h2>");
							$("#msj").css('display', 'block');
						},500);
					} 
				}
			},      
			columns: [
			{ "data": "Opciones" },
			{ "data": "Nombre Completo" },
			{ "data": "No Documento" },
			{ "data": "E-mail" },
			{ "data": "Usuario" },
			{ "data": "Rol" },
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
			cambiarContrasenia();
			eliminarUsuario();
			editarUsuario();
		});
	}

	$(function(){
		let archivo = "usuario.php";
		let pathname = window.location.pathname;
		let array = pathname.split("/");
		let archivo2 = array.pop ();

		if(archivo == archivo2){
			if ( $.fn.dataTable.isDataTable('#table_usuarios') ) {
				tabla.destroy();
				tabla_usuarios();
			}
			else {
				tabla_usuarios();
			}
		}
	});

	function cambiarContrasenia() {
		$("#table_usuarios").find(".reset").each(function() {
			$(this).unbind('click');
			$(this).click(function(){
				$("#btnChangePass").attr('data-idUsu', $(this).attr('data-idUsu'));

				$("#changePasswordModal").modal('show');
			})
		})
	}

	$("#changePasswordModal").on('hidden.bs.modal', function() {
		$("#formChangePass")[0].reset();
		$("#btnChangePass").removeAttr('data-idUsu');
	});

	$('#formChangePass').validate({
		rules: {
			newPassword: {
				required: true,
				minlength: 10
			},
			newPasswordC: {
				required: true,
				minlength: 10,
				equalTo: '#newPassword'
			},
		},
		messages: {
			newPassword: {
				required: 'Por favor, ingrese una nueva contraseña',
				minlength: 'Mínimo 10 caracteres'
			},
			newPasswordC: {
				required: 'Por favor, ingrese la confirmación de la nueva contraseña',
				minlength: 'Mínimo 10 caracteres',
				equalTo: 'Las contraseñas no coinciden'
			},
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
				data: $("#formChangePass").serialize() + `&idUsu=${$("#btnChangePass").attr('data-idUsu')}&peticion=actualizarPassword`,
				dataType: "json",
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Espere...',0);
					$("#btnChangePass").attr('disabled', 'disabled');
				}
			}).done(function( data ){
				alertify.dismissAll();
				$("#btnChangePass").removeAttr('disabled');
				if (data.exito) {
					$("#changePasswordModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formChangePass")[0].reset();
					alertas_s(data.msj);
				} else {
					alertas_e(data.msj);
				}
			});

		}
	});

	function editarUsuario() {
		$("#table_usuarios").find(".editar").each(function() {
			$(this).unbind('click');
			$(this).click(function(){
				$("#btnEdit").attr({
					"data-idUsu": $(this).attr("data-idUsu"),
					"data-idPer": $(this).attr("data-idPer")
				});

				$.ajax({
					type: "POST",
					url: "../../controllers/controllersAdministrador.php",
					data: {
						peticion: 'traerInformacionUsuario',
						idPer: $(this).attr("data-idPer")
					},
					dataType: "json",
					beforeSend:function(xhr){
						
					}
				}).done(function( data ){
					if (data.exito) {
						
						$("#edit-nombreC").val(data.nombre);
						$("#edit-documento").val(data.documento);
						$("#edit-email").val(data.email);
						$("#edit-celular").val(data.celular);
						$("#edit-idRol").val(data.idRol);

						$("#editUserModal").modal('show');
					} else {
						alertas_e(data.msj);
					}
				});
			})
		})
	}

	$("#editUserModal").on('hidden.bs.modal', function() {
		$("#formEditUser")[0].reset();
		$("#btnEdit").removeAttr('data-idUsu');
		$("#btnEdit").removeAttr('data-idPer');
	});

	$('#formEditUser').validate({
		rules: {
			"edit-nombreC": 'required',
			"edit-documento": 'required',
			"edit-email": {
				required: true,
				email: true
			},
			"edit-celular": 'required',
			"edit-idRol": 'required',
		},
		messages: {
			"edit-nombreC": 'Por favor, ingrese el nombre completo',
			"edit-documento": 'Por favor, ingrese el no. de documento',
			"edit-email": {
				required: 'Por favor, ingrese el E-mail',
				email: 'E-mail inválido'
			},
			"edit-celular": 'Por favor, ingrese el no. de celular',
			"edit-idRol": 'Por favor, seleccione un rol',
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
			let idPer = $("#btnEdit").attr('data-idPer');
			let idUsu = $("#btnEdit").attr('data-idUsu');

			$.ajax({
				type: "POST",
				url: "../../controllers/controllersAdministrador.php",
				data: $("#formEditUser").serialize()+`&idPer=${idPer}&idUsu=${idUsu}&peticion=actualizarUsuario`,
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
					$("#editUserModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formEditUser")[0].reset();
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
					peticion : 'eliminarUsuario',
					idUsu : eliminarSi['idUsu'],
					idPer : eliminarSi['idPer'],
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
		"idUsu" : "",
		"idPer" : ""
	}

	function eliminarUsuario(){
		$("#table_usuarios").find(".eliminar").each(function(){

			$(this).unbind("click");
			$(this).click(function(){
				eliminarSi['idUsu'] = $(this).attr("data-idUsu");
				eliminarSi['idPer'] = $(this).attr("data-idPer");
				deleteds( "Eliminar Usuario", "<h6><i class=\"fas fa-check\"></i> Seguro que desea eliminar el Usuario</h6><h6><i class=\"fas fa-check\"></i> Si elimina el Usuario no podrá recuperarlo nuevamente</h6>", eliminarSi['funcion'] , null );
			});
		});
	};

});