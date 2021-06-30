$(document).ready(function(){
  // Login
  let frm_login = $("#loginform");

  $('#loginform').validate({
    rules: {
      documento: 'required',
      pass: 'required'
    },
    messages: {
      documento: 'Por favor, ingrese un Usuario',
      pass: 'Por favor, ingrese una Contraseña'
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
        url: "iniciar_sesion.php",
        method: "POST",
        data: frm_login.serialize(),
        dataType: "JSON",
        success: function(response) {
          if(response.exito === true) {
            /*alertify.success(response.msj);*/
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

            toastr["success"](response.msj)

            setTimeout(function() {
              window.location.href = response.destino;
            }, 1200);

          } else {
            /*alertify.error(response.msj);*/
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

            toastr["error"](response.msj)
          }
        },
        error: function(xhr) {
          console.log(xhr.responseText);
        }
      });

    }
  });
});