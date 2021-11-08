<?php 
session_start();
if( !isset( $_SESSION['user']['nombrePersona'] ) || $_SESSION['user']['rol'] != 2 ){
	session_destroy();
	header('Location: ../../login.php');  
}

include "../../models/mixtas.php";

$mx = new Mixtas();
$lista_entidades = $mx->listadoentidadesbyPersona($_SESSION['user']['idPer']);
$lista_anio = $mx->selectAnio();
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Cultivo Sena Atlántico | Vivero</title>
	<meta name="description" content="Cultivo Sena Atlántico">
	<meta name="robots" content="all,follow">
	<?php include "../../comunes/head.php" ?>
	<link rel="stylesheet" href="../../assets/vendor/apexcharts/apexcharts.css">
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
							<small>Vivero</small>
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
									<h3 class="h6 text-uppercase mb-0">Seguimiento de Cultivos</h3>
								</div>
								<div class="card-body">
									<p>Seguimiento por cultivo mediante graficas de barras por tiempo transcurrido</p>
									<div class="row justify-content-end">

										<div class="col-12 col-md-3">
											<div class="form-group">
												<label class="form-control-label text-uppercase">Entidades</label>
												<select name="entidades" id="entidades" class="form-control">
													<option value="0" selected>Seleccione una entidad</option>
													<?php foreach ($lista_entidades as $row): ?>
														<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombreLugar']) ?></option>
													<?php endforeach ?>
												</select>
											</div>
										</div>

										<div class="col-12 col-md-3">
											<div class="form-group">
												<label class="form-control-label text-uppercase">Cultivos</label>
												<select name="cultivo" id="cultivo" class="form-control">
													<option value="0" selected>Seleccione un cultivo</option>
												</select>
											</div>
										</div>

										<div class="col-12 col-md-3">
											<div class="form-group">
												<label class="form-control-label text-uppercase">Año</label>
												<select name="anio" id="anio" class="form-control">
													<option value="0" selected>Seleccione un año</option>
													<?php echo $lista_anio ?>
													<option value="all">Todos</option>
												</select>
											</div>
										</div>

										<div class="col-12 col-md-3">
											<div class="form-group row">
												<label class="col-12 form-control-label text-uppercase">Tipo de Resultado</label>
												<div class="col-12">
													<div class="custom-control custom-radio">
														<input value="1" id="rmes" type="radio" name="tbusqueda" class="custom-control-input" checked>
														<label for="rmes" class="custom-control-label">Meses</label>
													</div>
													<div class="custom-control custom-radio">
														<input value="2" id="rdias" type="radio" name="tbusqueda" class="custom-control-input">
														<label for="rdias" class="custom-control-label">Días</label>
													</div>
												</div>
											</div>
										</div>

										<div class="col-12 col-md-2">
											<div class="form-group">  
												<label class="form-control-label text-uppercase"></label>     
												<button type="button" id="btnGrafica" class="btn btn-success btn-block">Consultar</button>
											</div>
										</div>

									</div>

									<div class="row">
										<div class="col-12">
											<hr>
										</div>
									</div>	

									<div class="row" id="lista_graficas">

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
<script src="../../assets/vendor/apexcharts/apexcharts.min.js"></script>

<script src="../../assets/js/app/vivero/seguimiento.js"></script>
</html>