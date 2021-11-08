$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	$("#btnTablas").click(function(){
		var entidad = $("#entidades").val();
		var cultivo = $("#cultivo").val();
		
		if (entidad != 0 && cultivo != 0) {

			$.ajax({
				type: 'POST',
				url: '../../controllers/controllersAdministrador.php',
				data: {
					peticion: 'consultaTablas',
					entidad: entidad,
					cultivo: cultivo
				},
				dataType: 'json',
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Por favor espere...',0);
					//$("#btnTablas").attr("disabled","disabled");
				},
			}).done(function(data) {
				alertify.dismissAll();
				//$("#btnTablas").removeAttr("disabled");
				
				if (data.exito) {
					$("#lista_tablas").empty();
					$("#lista_tablas").html(data.html);
				} else {
					$("#lista_tablas").empty();
					alertas_e("Fallo en el sistema");
				}
			});
			
		} else {
			$("#lista_tablas").empty();
			alertas_w("Por favor, llenar todos los campos de busqueda");
		}
	});

	$("#entidades").on('change', function() {
		$("#lista_tablas").empty();

		$.ajax({
			type: 'POST',
			url: '../../controllers/controllersAdministrador.php',
			data: {
				peticion: 'consultaCultivos',
				all: 0,
				entidad: $(this).val()
			},
			dataType: 'json',
			beforeSend:function(xhr){
				
			},
		}).done(function(data) {
			if (data.exito) {
				$("#cultivo").empty();
				$("#cultivo").html(data.html);
			} else {
				$("#cultivo").empty();
				$("#cultivo").html(data.html);

				alertas_w(data.msj);
			}
		});
	});

	$("#cultivo").on('change', function() {
		$("#lista_tablas").empty();
	});

});