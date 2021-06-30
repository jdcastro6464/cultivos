$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	Apex.chart = {
		locales: [{
			"name": "es",
			"options": {
				"months": [
				"Enero",
				"Febrero",
				"Marzo",
				"Abril",
				"Mayo",
				"Junio",
				"Julio",
				"Agosto",
				"Septiembre",
				"Octubre",
				"Noviembre",
				"Diciembre"
				],
				"shortMonths": [
				"Ene",
				"Feb",
				"Mar",
				"Abr",
				"May",
				"Jun",
				"Jul",
				"Ago",
				"Sep",
				"Oct",
				"Nov",
				"Dic"
				],
				"days": [
				"Domingo",
				"Lunes",
				"Martes",
				"Miércoles",
				"Jueves",
				"Viernes",
				"Sábado"
				],
				"shortDays": ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
				"toolbar": {
					"exportToSVG": "Descargar SVG",
					"exportToPNG": "Descargar PNG",
					"exportToCSV": "Descargar CSV",
					"menu": "Menu",
					"selection": "Seleccionar",
					"selectionZoom": "Seleccionar Zoom",
					"zoomIn": "Aumentar",
					"zoomOut": "Disminuir",
					"pan": "Navegación",
					"reset": "Reiniciar Zoom"
				}
			}
		}],
		defaultLocale: "es"
	}


	function crearGraficaxy( series, title, int, format_x, format_tooltip ) {
		var options = {
			series: series,
			chart: {
				height: 350,
				type: 'scatter',
				zoom: {
					type: 'xy'
				}
			},
			title: {
				text: title
			},
			dataLabels: {
				enabled: false
			},
			grid: {
				xaxis: {
					lines: {
						show: true
					}
				},
				yaxis: {
					lines: {
						show: true
					}
				},
			},
			xaxis: {
				type: 'datetime',
				labels:{
					format: format_x
				}
			},
			tooltip: {
				x:{
					format: format_tooltip
				}
			}
		};

		var chart = new ApexCharts(document.querySelector("#chart_"+int), options);
		chart.render();
	}

	$("#btnGrafica").click(function(){
		var entidad = $("#entidades").val();
		var cultivo = $("#cultivo").val();
		var anio = $("#anio").val();
		var tbusqueda = $("[name=tbusqueda]:checked").val();

		if (entidad != 0 && cultivo != 0 && anio != 0 && tbusqueda != 0) {

			$.ajax({
				type: 'POST',
				url: '../../controllers/controllersAdministrador.php',
				data: {
					peticion: 'consultaHistorico',
					entidad: entidad,
					cultivo: cultivo,
					anio: anio,
					tbusqueda: tbusqueda
				},
				dataType: 'json',
				beforeSend:function(xhr){
					alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Por favor espere...',0);
					$("#btnGrafica").attr("disabled","disabled");
				},
			}).done(function(data) {
				alertify.dismissAll();
				$("#btnGrafica").removeAttr("disabled");
				
				if (data.exito) {
					if (data.info) {
						$("#lista_graficas").empty();
						
						for (var i = 0; i < data.msj.length; i++) {
							$("#lista_graficas").append('<div class="col-12 mb-5"><div id="chart_'+i+'"></div></div>');
						}

						for (var i = 0; i < data.msj.length; i++) {
							if (tbusqueda == 1) {
								crearGraficaxy( data.msj[i]['datas'], data.msj[i]['title'], i, "yyyy-MM", "MMMM, yyyy" );
							} else if(tbusqueda == 2) {
								crearGraficaxy( data.msj[i]['datas'], data.msj[i]['title'], i, "yyyy-MM-dd", "dddd, dd MMMM, yyyy" );
							}
						}
					} else {
						$("#lista_graficas").empty();
						$("#lista_graficas").html(data.msj);
					}
				} else {
					$("#lista_graficas").empty();
					alertas_e(data.msj);
				}
			});
			
		} else {
			$("#lista_graficas").empty();
			alertas_w("Por favor, llenar todos los campos de busqueda");
		}
	});

	$("#entidades").on('change', function() {
		$("#lista_graficas").empty();

		$.ajax({
			type: 'POST',
			url: '../../controllers/controllersAdministrador.php',
			data: {
				peticion: 'consultaCultivos',
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
		$("#lista_graficas").empty();
	});

	$("#anio").on('change', function() {
		$("#lista_graficas").empty();
	});

	$("[name=tbusqueda]").on('change', function() {
		$("#lista_graficas").empty();
	});

});