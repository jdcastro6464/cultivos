<?php 
session_start();
if(isset($_SESSION['user']['rol'])){
  header("Location:".$_SESSION["destino"]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Cultivo Sena Atlántico | Iniciar Sesión</title>
	<meta name="description" content="Cultivo Sena Atlántico">
	<link rel="icon" href="assets/img/favicon.png" sizes="32x32" type="image/png">
  <!-- custom.css -->
  <link rel="stylesheet" href="assets/css/custom.css">
  <!-- bootstrap.min.css -->
  <link rel="stylesheet" href="assets/vendor/bootstrap-4.1.3/css/bootstrap.min.css">
  <!-- font-awesome -->
  <link rel="stylesheet" href="assets/vendor/@fortawesome/fontawesome-free/css/all.min.css">
  <!-- AOS -->
  <link rel="stylesheet" href="assets/vendor/aos/aos.css">
  <!-- estilo del modal -->
  <link href="assets/vendor/sweetalert/sweetalert.css" rel="stylesheet">
  <!-- Toastr Css -->
  <link rel="stylesheet" href="assets/vendor/toastr/css/toastr.min.css">
  <!-- Alertify Css -->
  <link rel="stylesheet" href="assets/vendor/alertify/css/alertify.min.css">
  <link rel="stylesheet" href="assets/vendor/alertify/css/themes/default.min.css">
  <link rel="stylesheet" href="assets/vendor/alertify/css/themes/semantic.min.css">
  <link rel="stylesheet" href="assets/vendor/alertify/css/themes/bootstrap.min.css">
  <!-- custom.css -->
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
	<main class="d-flex align-items-center min-vh-100">
		<div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-9 my-5">
          <div class="card login-card">
            <div class="row no-gutters">
              <div class="d-none d-md-block col-md-6">
                <img src="assets/img/contact-bk4.jpg" alt="login" class="login-card-img">
              </div>
              <div class="col-12 col-md-6 py-3">
                <div class="card-body">
                  <div class="brand-wrapper text-center">
                    <a href="index.php"><img src="assets/img/logo.png" alt="logo" class="img-fluid mx-auto" style="height: 60px;"></a>
                  </div>
                  <p class="login-card-description mb-1">Iniciar sesión</p>
                  <form id="loginform">

                    <div class="form-group">
                      <label for="documento" class="sr-only">Usuario</label>
                      <input type="text" name="documento" id="documento" class="form-control" placeholder="Usuario">
                    </div>

                    <div class="form-group">
                      <label for="pass" class="sr-only">Contraseña</label>
                      <input type="password" name="pass" id="pass" class="form-control" placeholder="Contraseña">
                    </div>

                    <button id="btnLogin" class="btn btn-block login-btn mb-4" type="submit">Iniciar Sesión</button>

                  </form>
                  <a href="#!" class="forgot-password-link">¿Olvide Contraseña?</a>

                  <nav class="login-card-footer-nav mt-4">
                    <a href="http://sennovacolomboaleman.blogspot.com/" target="_blank">Copyright © <?php echo date("Y") ?> Sennova CNCA.</a>
                  </nav>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

</body>
<script src="assets/vendor/jquery-3.4.1/jquery-3.4.1.min.js"></script>
<script src="assets/vendor/jquery-3.4.1/popper.min.js"></script>
<script src="assets/vendor/bootstrap-4.1.3/js/bootstrap.min.js"></script>
<!-- Texto Modal -->
<script src="assets/js/functions.js"></script>
<!-- alerta conctato -->
<script src="assets/vendor/sweetalert/sweetalert.min.js"></script>
<!-- AOS -->
<script src="assets/vendor/aos/aos.js"></script>
<script>
	AOS.init({
	});
</script>
<!-- Toastr JS -->
<script src="assets/vendor/toastr/js/toastr.js"></script>
<!-- Alertify JS -->
<script src="assets/vendor/alertify/js/alertify.min.js"></script>
<script src="assets/vendor/jquery-validation/js/jquery.validate.js"></script>

<script src="assets/js/app/main.js"></script>
</html>