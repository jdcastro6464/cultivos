jQuery(document).ready(function($) {
	$.getScript( "../../assets/js/app/validaciones.js" );

	let tabla;

	function tabla_contacto(){
		tabla = $('#table_contactenos').DataTable({
			order : [],
			select: true,
			searching: true,
			bDeferRender: true,     
			sPaginationType: "full_numbers",
			ajax: {
				url: "../../controllers/controllersAdministrador.php",
				type: "POST",
				data: {
					peticion : "listarContactenos"
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
							$("#msj").html("<h2>¡No hay mensajes!</h2>");
							$("#msj").css('display', 'block');
						},500);
					} 
				}
			},      
			columns: [
			{ "data": "Opciones" },
			{ "data": "Nombre" },
			{ "data": "E-mail" },
			{ "data": "Mensaje" },
			{ "data": "Fecha" },
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
			marcarVisto();
		});
	}

	$(function(){
		let archivo = "contactenos.php";
		let pathname = window.location.pathname;
		let array = pathname.split("/");
		let archivo2 = array.pop ();

		if(archivo == archivo2){
			if ( $.fn.dataTable.isDataTable('#table_contactenos') ) {
				tabla.destroy();
				tabla_contacto();
			}
			else {
				tabla_contacto();
			}
		}
	});

	function marcarVisto() {
		$("#table_contactenos").find(".visto").each(function() {
			$(this).unbind('click');
			$(this).click(function(){
				
				$.ajax({
					type: "POST",
					url: "../../controllers/controllersAdministrador.php",
					data: {
						peticion: 'marcarComoVisto',
						idContacto: $(this).attr('data-idContacto')
					},
					dataType: "json",
					beforeSend:function(xhr){
						alertify.warning('<i class="fa fa-spinner fa-pulse fa-lg fa-fw"></i>Espere...',0);
					}
				}).done(function( data ){
					alertify.dismissAll();
					
					if (data.exito) {
						tabla.ajax.reload();
						alertas_s(data.msj);
					} else {
						alertas_e(data.msj);
					}
				});

			})
		})
	}

});