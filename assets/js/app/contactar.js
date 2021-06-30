$(document).ready(function() {
	$.getScript( "assets/js/app/validaciones.js" );

	$("#form_contacta").validate({
		rules: {
			name: 'required',
			correo: {
				required: true,
				email: true
			},
			message: 'required',
		},
		messages: {
			name: 'Por favor, ingrese el nombre',
			correo: {
				required: 'Por favor, ingresar un correo electrónico',
				email: 'Correo electrónico no válido'
			},
			message: 'Por favor, ingrese un mensaje',
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
				url: "controllers/controllersAdministrador.php",
				data: $("#form_contacta").serialize() + "&peticion=agregarContactame",
				dataType: "json",
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Por favor espere...',0);
					$("#btnContact").attr('disabled', 'disabled');
				}
			}).done(function( data ){
				alertify.dismissAll();
				$("#btnContact").removeAttr('disabled');
				if (data.exito) {
					$("#form_contacta")[0].reset();
					swal("¡Enviado!", data.msj, "success");
				} else {
					swal("¡Lo Sentimo!",data.msj,"error");
				}
			});
		}
	});
	
});