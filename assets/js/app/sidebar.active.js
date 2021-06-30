$(document).ready(function() {
	
	$(function(){
		$("#sidebar").find('.sidebar-link').each(function() {
			$(this).removeClass("active");
		});

		let pathname = window.location.pathname;
		let array = pathname.split("/");
		let namepage = array.pop ();

		if(namepage == "index.php" || namepage == ""){
			$(".inicio").addClass('active');
		} else if(namepage == "seguimiento.php"){
			$(".seguimiento").addClass('active');
		} else if(namepage == "prediccion.php"){
			$(".prediccion").addClass('active');
		}
	});

});