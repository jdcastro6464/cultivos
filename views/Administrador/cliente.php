<?php 
session_start();
if( !isset( $_SESSION['user']['nombrePersona'] ) || $_SESSION['user']['rol'] != 1 ){
	session_destroy();
	header('Location: ../../login.php');  
}

include "../../models/mixtas.php";

$mx = new Mixtas();

$listDepart = $mx->listadoDepartamentos();
$listPerson = $mx->listadoClientes();
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
									<div class="row justify-content-between">
										<div class="col align-self-center">
											<h3 class="h6 text-uppercase mb-0">Listado de Entidades</h3>
										</div>
										<div class="col text-right">
											<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#newClientModal">Nueva Entidad</button>
										</div>
									</div>
								</div>
								<div class="card-body">
									
									<div id="aprobacion">
										<div class="data-tables table-responsive">
											<table id="table_clientes" class="table text-center w-100">
												<thead class="bg-gray-900 text-capitalize">
													<tr>
														<th>Opciones</th>
														<th>Cliente</th>
														<th>Nombre</th>
														<th>Dirección</th>
														<th>Departamento</th>
														<th>Ciudad</th>
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

			<!-- New User Modal Start -->
			<div class="modal fade" id="newClientModal" tabindex="-1" aria-labelledby="labelNewClient" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="labelNewClient">Nueva Entidad</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form id="formNewClient">
							<div class="modal-body">
								<div class="row">
									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Nombre</label>
											<input type="text" id="nombre" name="nombre" placeholder="Nombre del Lugar" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Dirección</label>
											<input type="text" id="direccion" name="direccion" placeholder="Dirección" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Cliente</label>
											<select id="idPersona" name="idPersona" class="form-control">
												<option value="" selected>Seleccione un Cliente</option>
												<?php foreach ($listPerson as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Departamento</label>
											<select id="idDepartamento" name="idDepartamento" class="form-control">
												<option value="" selected>Seleccione un Departamento</option>
												<?php foreach ($listDepart as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Ciudad</label>
											<select id="idCiudad" name="idCiudad" class="form-control">
												<option value="" selected>Seleccione una Ciudad</option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" id="btnAdd" class="btn btn-primary">Agregar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- New User Modal End -->

			<!-- Edit User Modal Start -->
			<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="labelEditClient" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="labelEditClient">Editar Entidad</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form id="formEditClient">
							<div class="modal-body">
								<div class="row">
									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Nombre</label>
											<input type="text" id="edit-nombre" name="edit-nombre" placeholder="Nombre del Lugar" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Dirección</label>
											<input type="text" id="edit-direccion" name="edit-direccion" placeholder="Dirección" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Cliente</label>
											<select id="edit-idPersona" name="edit-idPersona" class="form-control">
												<option value="" selected>Seleccione un Cliente</option>
												<?php foreach ($listPerson as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Departamento</label>
											<select id="edit-idDepartamento" name="edit-idDepartamento" class="form-control">
												<option value="" selected>Seleccione un Departamento</option>
												<?php foreach ($listDepart as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
											</select>
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Ciudad</label>
											<select id="edit-idCiudad" name="edit-idCiudad" class="form-control">
												<option value="" selected>Seleccione una Ciudad</option>
											</select>
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" id="btnEdit" class="btn btn-primary">Editar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- Edit User Modal End -->

			<?php include "../../comunes/footer.php" ?>
		</div>
	</div>
</body>
<!-- JavaScript files-->
<?php include "../../comunes/script.php" ?>

<script src="../../assets/js/app/administrador/cliente.js"></script>
</html>