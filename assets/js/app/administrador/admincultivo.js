$(document).ready(function() {
	$.getScript( "../../assets/js/app/validaciones.js" );

	$('#formNewCultivo').validate({
		rules: {
			idEntidad: 'required',
			idCultivo: 'required',
		},
		messages: {
			idEntidad: 'Por favor, seleccione una entidad',
			idCultivo: 'Por favor, seleccione un cultivo',
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
				data: $("#formNewCultivo").serialize()+"&peticion=agregarCultivo",
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
					$("#newCultivoModal").modal("hide");
					$('.modal-backdrop').remove();
					$("#formNewCultivo")[0].reset();
					alertas_s(data.msj);
				} else {
					alertas_e(data.msj);
				}
			});

		}
	});

	let tabla;

	function tabla_cultivos(idEnti){
		tabla = $('#table_cultivos').DataTable({
			order : [],
			select: true,
			searching: true,
			bDeferRender: true,     
			sPaginationType: "full_numbers",
			ajax: {
				url: "../../controllers/controllersAdministrador.php",
				type: "POST",
				data: {
					peticion : "listarCultivos",
					idEnti: idEnti
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
							$("#msj").html("<h2>¡No hay cultivos almacenados!</h2>");
							$("#msj").css('display', 'block');
						},500);
					} 
				}
			},      
			columns: [
			{ "data": "Opciones" },
			{ "data": "Cultivo" },
			{ "data": "Hectáreas" },
			{ "data": "Metros Cuadrados" },
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
			eliminarCultivo();
		});
	}

	$("#btnSearch").click(function(){
		if( $("#entidades").val() > 0 ) {
			if ( $.fn.dataTable.isDataTable('#table_cultivos') ) {
				tabla.destroy();
				tabla_cultivos($("#entidades").val());
			}
			else {
				tabla_cultivos($("#entidades").val());
			}
		} else {
			$("#msj").css('display', 'none');
			$("#msj").empty();
			$("#aprobacion").css('display', 'none');
			alertas_w('Por favor, seleccione una entidad');
		}
	});

	let eliminarSi = {
		"funcion" : function(){
			$.ajax({
				type: 'POST',
				url: '../../controllers/controllersAdministrador.php',
				data: {
					peticion : 'eliminarCultivo',
					idZonCultivo : eliminarSi['idZonCultivo'],
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
		"idZonCultivo" : ""
	}

	function eliminarCultivo(){
		$("#table_cultivos").find(".eliminar").each(function(){

			$(this).unbind("click");
			$(this).click(function(){
				eliminarSi['idZonCultivo'] = $(this).attr("data-idZonaCul");
				deleteds( "Eliminar Cultivo", "<h6><i class=\"fas fa-check\"></i> Seguro que desea eliminar el Cultivo</h6><h6><i class=\"fas fa-check\"></i> Si elimina el Cultivo no podrá recuperarlo nuevamente</h6>", eliminarSi['funcion'] , null );
			});
		});
	};

});