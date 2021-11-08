$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	$("#cont_942b26e81183f2170e5be8c8b9d2616a").css({
		position: 'relative'
	});

	$(window).resize(function(){
		if ( $(window).width() <= 500 ) {
			$(".content_clima").find("iframe").each(function(index, el) {
				$(this).css('width', '250px');
				$(this).removeAttr("scrolling");
				$(this).attr("scrolling","si");
			});
		} else {
			$(".content_clima").find("iframe").each(function(index, el) {
				$(this).css('width', '776px');
				$(this).removeAttr("scrolling");
				$(this).attr("scrolling","no");
			});
		}
	});

	function bloqueo_variables() {
		$("#val_humedad").attr("disabled","disabled");
		$("#val_luminosidad").attr("disabled","disabled");
		$("#val_nitrogeno").attr("disabled","disabled");
		$("#val_potasio").attr("disabled","disabled");
		$("#val_fosforo").attr("disabled","disabled");
		$("#val_acidez").attr("disabled","disabled");
		$("#val_temp").attr("disabled","disabled");
	}

	function empty_variables() {
		$("#val_humedad").val("")
		$("#val_luminosidad").val("")
		$("#val_nitrogeno").val("")
		$("#val_potasio").val("")
		$("#val_fosforo").val("")
		$("#val_acidez").val("")
		$("#val_temp").val("")
	}

	$("#idVariable").on('change', function(event) {
		var idCultivo = $("#idCultivo").val();
		if (idCultivo == 7 && $(this).val() == 19) {
			alertas_w("Variable no valida para el cultivo seleccionado");
			$(this).val("");
			bloqueo_variables();
		} else if(idCultivo == 11 && $(this).val() == 20) {
			alertas_w("Variable no valida para el cultivo seleccionado");
			$(this).val("");
			bloqueo_variables();
		} else {
			if ( $(this).val() == 17 ) {

				$("#val_humedad").attr("disabled","disabled");
				$("#val_luminosidad").removeAttr("disabled");
				$("#val_nitrogeno").removeAttr("disabled");
				$("#val_potasio").removeAttr("disabled");
				$("#val_fosforo").removeAttr("disabled");
				$("#val_acidez").removeAttr("disabled");
				$("#val_temp").removeAttr("disabled");

			} else if( $(this).val() == 18 ) {

				$("#val_humedad").removeAttr("disabled");
				$("#val_luminosidad").attr("disabled", "disabled");
				$("#val_nitrogeno").removeAttr("disabled");
				$("#val_potasio").removeAttr("disabled");
				$("#val_fosforo").removeAttr("disabled");
				$("#val_acidez").removeAttr("disabled");
				$("#val_temp").removeAttr("disabled");

			} else if( $(this).val() == 19 ) {

				$("#val_humedad").removeAttr("disabled");
				$("#val_luminosidad").removeAttr("disabled");
				$("#val_nitrogeno").attr("disabled", "disabled");
				$("#val_potasio").removeAttr("disabled");
				$("#val_fosforo").removeAttr("disabled");
				$("#val_acidez").removeAttr("disabled");
				$("#val_temp").removeAttr("disabled");

			} else if( $(this).val() == 20 ) {

				$("#val_humedad").removeAttr("disabled");
				$("#val_luminosidad").removeAttr("disabled");
				$("#val_nitrogeno").removeAttr("disabled");
				$("#val_potasio").attr("disabled", "disabled");
				$("#val_fosforo").removeAttr("disabled");
				$("#val_acidez").removeAttr("disabled");
				$("#val_temp").removeAttr("disabled");

			} else if( $(this).val() == 21 ) {

				$("#val_humedad").removeAttr("disabled");
				$("#val_luminosidad").removeAttr("disabled");
				$("#val_nitrogeno").removeAttr("disabled");
				$("#val_potasio").removeAttr("disabled");
				$("#val_fosforo").attr("disabled", "disabled");
				$("#val_acidez").removeAttr("disabled");
				$("#val_temp").removeAttr("disabled");

			} else if( $(this).val() == 22 ) {

				$("#val_humedad").removeAttr("disabled");
				$("#val_luminosidad").removeAttr("disabled");
				$("#val_nitrogeno").removeAttr("disabled");
				$("#val_potasio").removeAttr("disabled");
				$("#val_fosforo").removeAttr("disabled");
				$("#val_acidez").attr("disabled", "disabled");
				$("#val_temp").removeAttr("disabled");

			} else {
				bloqueo_variables();
			}
		}

		if ( $("#idCultivo").val() == 7 ) {
			$("#val_nitrogeno").attr('disabled', 'disabled');
		} else if( $("#idCultivo").val() == 11 ) {
			$("#val_potasio").attr('disabled', 'disabled');
		}

		empty_variables();
	});

	$("#idCultivo").on('change', function(event) {
		bloqueo_variables();

		$("#idVariable").val("");
		empty_variables();
	});

	if ( $("#idCultivo").val() == 7 ) {
		$("#val_nitrogeno").attr('disabled', 'disabled');
	} else if( $("#idCultivo").val() == 11 ) {
		$("#val_potasio").attr('disabled', 'disabled');
	}

	$("#form_variables").validate({
		rules: {
			idCultivo: 'required',
			idVariable: 'required',
			val_humedad: 'required',
			val_luminosidad: 'required',
			val_nitrogeno: 'required',
			val_potasio: 'required',
			val_fosforo: 'required',
			val_acidez: 'required',
			val_temp: 'required',
		},
		messages: {
			idCultivo: 'Por favor, seleccione un cultivo',
			idVariable: 'Por favor, seleccione una variable a predecir',
			val_humedad: 'Campo Obligatorio*',
			val_luminosidad: 'Campo Obligatorio*',
			val_nitrogeno: 'Campo Obligatorio*',
			val_potasio: 'Campo Obligatorio*',
			val_fosforo: 'Campo Obligatorio*',
			val_acidez: 'Campo Obligatorio*',
			val_temp: 'Campo Obligatorio*',
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
				data: {
					peticion: "prediccion_file",
					idCultivo: $("#idCultivo").val()
				},
				dataType: "json",
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Creando CSV...',0);
					$("#btnConsulta").attr('disabled', 'disabled');
				}
			}).done(function( data ){
				alertify.dismissAll();
				$("#btnConsulta").removeAttr('disabled');
				if (data.exito) {

					$.ajax({
						type: 'POST',
						url: 'http://127.0.0.1:5000/prediccion_variable',
						//url: 'http://colomboalemanbq.com/webflask/prediccion_variable',
						dataType: 'json',
						data: $("#form_variables").serialize(),
						beforeSend:function(xhr){
							alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Prediccion de variable...',0);
							$("#btnConsulta").attr('disabled', 'disabled');
						}
					}).done(function(data) {
						alertify.dismissAll();
						$("#btnConsulta").removeAttr('disabled');
						$("#msj_value").empty();

						var resultado = Math.round(data.prediccion_value[0] * 100) / 100;
						var idVariable = $("#idVariable").val();
						var texto

						if (idVariable == 17) {
							texto = " %";
						} else if (idVariable == 18) {
							texto = " hrs de luz/días";
						} else if (idVariable == 19) {
							texto = " Kg";
						} else if (idVariable == 20) {
							texto = " Kg";	
						} else if (idVariable == 21) {
							texto = " Kg";
						} else if (idVariable == 22) {
							texto = " pH";
						}
						$("#msj_value").html(resultado+texto);
					});

				} else {
					alertas_e( data.msj );
				}
			});
		}
	});

});