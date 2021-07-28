$(document).ready(function() {
	
	$(function(){
		$("#sidebar").find('.sidebar-link').each(function() {
			$(this).removeClass("active");
		});

		let pathname = window.location.pathname;
		let array = pathname.split("/");
		let namepage = array.pop ();

		$("#pages").removeClass('show');
		$("#expand").attr('aria-expanded','false');

		$("#pages_r").removeClass('show');
		$("#expand_r").attr('aria-expanded','false');

		if(namepage == "index.php" || namepage == ""){
			$(".inicio").addClass('active');
		} else if(namepage == "seguimiento.php"){
			$(".seguimiento").addClass('active');
		} else if(namepage == "prediccion.php"){
			$(".prediccion").addClass('active');
		} else if(namepage == "usuario.php"){
			$(".usuario").addClass('active');
			$("#pages").addClass('show');
			$("#expand").attr('aria-expanded','true');
		} else if(namepage == "contactenos.php"){
			$(".contactenos").addClass('active');
		} else if(namepage == "cliente.php"){
			$(".cliente").addClass('active');
			$("#pages").addClass('show');
			$("#expand").attr('aria-expanded','true');
		} else if(namepage == "admincultivo.php"){
			$(".admincultivo").addClass('active');
			$("#pages").addClass('show');
			$("#expand").attr('aria-expanded','true');
		} else if(namepage == "registros.php"){
			$(".registros").addClass('active');
			$("#pages_r").addClass('show');
			$("#expand_r").attr('aria-expanded','true');
		} else if(namepage == "registros_predi.php"){
			$(".registros_predi").addClass('active');
			$("#pages_r").addClass('show');
			$("#expand_r").attr('aria-expanded','true');
		}
	});

});