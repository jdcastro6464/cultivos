//Funciones para cada tipo de alerta e(error),w(warning),s(success)
function alertas_e( msg ) {
	/*alertify.error(msg);*/
	toastr.options = {
		"closeButton": false,
		"debug": false,
		"newestOnTop": false,
		"progressBar": false,
		"positionClass": "toast-bottom-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	}
	
	toastr["error"](msg)
}

function alertas_w( msg ) {
	/*alertify.warning(msg);*/
	toastr.options = {
		"closeButton": false,
		"debug": false,
		"newestOnTop": false,
		"progressBar": false,
		"positionClass": "toast-bottom-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	}

	toastr["warning"](msg)
}

function alertas_s( msg ) {
	/*alertify.success(msg);*/
	toastr.options = {
		"closeButton": false,
		"debug": false,
		"newestOnTop": false,
		"progressBar": false,
		"positionClass": "toast-bottom-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "5000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	}

	toastr["success"](msg)	
}
//Funcion Eliminar con Framework Alertify Js
function deleteds( $title, $msjConfirm, $functionSi, $functionNo ){
	if( $functionNo == null ){
		alertify.confirm( $title, $msjConfirm, $functionSi, function(){} );
	}else{
		alertify.confirm( $title, $msjConfirm, $functionSi, $functionNo );
	}
}