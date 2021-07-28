<?php 
session_start();
if( !isset( $_SESSION['user']['nombrePersona'] ) || $_SESSION['user']['rol'] != 1 ){
	session_destroy();
	header('Location: ../../login.php');  
}
include "../../models/mixtas.php";

$mx = new Mixtas();
$variables = $mx->listadoVariables();
$cultivos = $mx->listadoCultivos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cultivo Sena Atlántico | Administrador</title>
	<meta name="description" content="Cultivo Sena Atlántico">
	<meta name="robots" content="all,follow">
	<?php include "../../comunes/head.php" ?>
	<style>
	.form-control-label {
		font-size: .8rem;
	}
</style>
</head>
<body class="contenedor">
	<!-- navbar-->
	<header class="header">
		<nav class="navbar navbar-expand-lg px-4 py-2 bg-white shadow">
			<a href="#!" class="sidebar-toggler text-gray-500 mr-4 mr-lg-5 lead">
				<i class="fas fa-align-left"></i>
			</a>
			<a href="index.php" class="navbar-brand font-weight-bold text-uppercase text-base">
				Cultivo Sena Atlántico
			</a>
			<ul class="ml-auto d-flex align-items-center list-unstyled mb-0">
				<li class="nav-item dropdown ml-auto">
					<a id="userInfo" href="#!" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
						<img src="../../assets/img/perfil.jpg" alt="Avatar Perfil" style="max-width: 2.5rem;" class="img-fluid rounded-circle shadow">
					</a>
					<div aria-labelledby="userInfo" class="dropdown-menu">
						<a href="#!" class="dropdown-item">
							<strong class="d-block text-uppercase headings-font-family"><?php echo utf8_encode($_SESSION['user']['nombrePersona']) ?></strong>
							<small>Administrador</small>
						</a>
						<div class="dropdown-divider"></div>
						<a href="../../logout.php" class="dropdown-item">Cerrar Sesión</a>
					</div>
				</li>
			</ul>
		</nav>
	</header>
	<div class="d-flex align-items-stretch">
		<!-- Sidebar Start -->
		<?php include "comun/sidebar.php" ?>
		<!-- Sidebar End -->

		<div class="page-holder w-100 d-flex flex-wrap">
			<div class="container-fluid px-xl-5">
				<!-- Main Content Start -->
				<section class="py-5">	
					<div class="row">
						<div class="col-12">
							
							<div class="card">
								<div class="card-header">
									<h3 class="h6 text-uppercase mb-0">Predicción de Variables | Investigación</h3>
								</div>
								<div class="card-body">
									<div class="row mb-4">
										<div class="col-12 text-center">
											<h5 class="font-weight-light">Pronóstico del Tiempo</h5>
										</div>
										<!-- Predicción de Sabanalarga -->
										<!-- <div id="cont_942b26e81183f2170e5be8c8b9d2616a" class="col-12 text-center content_clima">
											<script type="text/javascript" async src="https://www.tiempo.com/wid_loader/942b26e81183f2170e5be8c8b9d2616a"></script>
										</div> -->

										<!-- Predicción de Barranquilla -->
										<div id="cont_e70fd6f305145359fa734f760e8c995b" class="col-12 text-center content_clima">
											<script type="text/javascript" async src="https://www.tiempo.com/wid_loader/e70fd6f305145359fa734f760e8c995b"></script>
										</div>
									</div>

									<p class="text-justify">Este es un módulo que permite predecir variables con ayuda de <b>Machine Learning;</b> la <b>técnica</b> estadística utilizada es el análisis de <b>regresión lineal.</b> El análisis de <b>regresión</b> se utiliza para predecir un amplio rango de fenómenos, desde medidas económicas hasta diferentes aspectos del comportamiento humano.<br>Esta técnica se desarrollo bajo el lenguaje de programación <b>Python.</b></p>
									
									<form id="form_variables">
										<div class="row mt-5">

											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Cultivos</label>
													<select name="idCultivo" id="idCultivo" class="form-control">
														<option value="" selected>Seleccione un cultivo</option>
														<?php foreach ($cultivos as $row): ?>
															<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
														<?php endforeach ?>
													</select>
												</div>
											</div>

											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Variable a Predecir</label>
													<select name="idVariable" id="idVariable" class="form-control">
														<option value="" selected>Seleccione una variable</option>
														<?php foreach ($variables as $row): ?>
															<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
														<?php endforeach ?>
													</select>
												</div>
											</div>

											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Humedad relativa [%]</label>
													<input disabled type="number" id="val_humedad" name="val_humedad" class="form-control">
												</div>
											</div>

											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Luminosidad [hrs de luz/días]</label>
													<input disabled type="number" id="val_luminosidad" name="val_luminosidad" class="form-control">
												</div>
											</div>

											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Nitrógeno (N) [Kg]</label>
													<input disabled type="number" id="val_nitrogeno" name="val_nitrogeno" class="form-control">
												</div>
											</div>
											
											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Potasio (K) [Kg]</label>
													<input disabled type="number" id="val_potasio" name="val_potasio" class="form-control">
												</div>
											</div>
											
											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Fósforo (P) [Kg]</label>
													<input disabled type="number" id="val_fosforo" name="val_fosforo" class="form-control">
												</div>
											</div>
											
											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Acidez del suelo [pH]</label>
													<input disabled type="number" id="val_acidez" name="val_acidez" class="form-control">
												</div>
											</div>
											
											<div class="col-12 col-md-3">
												<div class="form-group">
													<label class="form-control-label">Temperatura [°C]</label>
													<input disabled type="number" id="val_temp" name="val_temp" class="form-control">
												</div>
											</div>

										</div>
										<div class="row justify-content-end">

											<div class="col-12 col-md-2">
												<div class="form-group">  
													<label class="form-control-label text-uppercase"></label>     
													<button type="submit" id="btnConsulta" class="btn btn-success btn-block">Predecir</button>
												</div>
											</div>

										</div>
									</form>
									<div class="row">
										<div class="col-12">
											<hr>
										</div>

										<div class="col-12">
											<div class="bg-white shadow roundy p-4 h-100 d-flex align-items-center justify-content-between bg-hover-gradient-green">
												<div class="flex-grow-1 d-flex align-items-center">
													<div class="dot mr-3 bg-blue"></div>
													<div class="text">
														<h6 class="mb-0">La predicción obtenida en la variable seleccionada es:</h6><span id="msj_value" class="text-dark"></span>
													</div>
												</div>
												<div class="icon text-white bg-blue"><i class="fas fa-chart-bar"></i></div>
											</div>
										</div>

									</div>
								</div>
							</div>

						</div>
					</div>
				</section>				
				<!-- Main Content End -->
			</div>

			<?php include "../../comunes/footer.php" ?>
		</div>
	</div>
</body>
<!-- JavaScript files-->
<?php include "../../comunes/script.php" ?>

<script src="../../assets/js/app/administrador/prediccion.js"></script>
</html>