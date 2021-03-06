<?php 
	session_start();
	if( !isset( $_SESSION['user']['nombrePersona'] ) || $_SESSION['user']['rol'] != 2 ){
		session_destroy();
		header('Location: ../../login.php');  
	}

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
									<h3 class="h6 text-uppercase mb-0">Registros de Cultivos Predictores</h3>
								</div>
								<div class="card-body">
									
									<div id="aprobacion">
										<div class="data-tables table-responsive">
											<table id="table_registros" class="table text-center w-100">
												<thead class="bg-gray-900 text-capitalize">
													<tr>
														<th>Entidad</th>
														<th>Cultivo</th>
														<th>Humedad relativa</th>
														<th>Luminosidad</th>
														<th>Nitrógeno (N)</th>
														<th>Potasio (K)</th>
														<th>Fósforo (P)</th>
														<th>Acidez del suelo (pH)</th>
														<th>Temperatura</th>
														<th>Fecha Larga</th>
														<th>Fecha Corta</th>
														<th>Hora</th>
													</tr>
												</thead>
												<tbody>
												</tbody>
											</table>
										</div>
									</div>
									<div id="msj"></div>

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

<script src="../../assets/js/app/vivero/registros_predi.js"></script>
</html>