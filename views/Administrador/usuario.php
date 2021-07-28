<?php 
session_start();
if( !isset( $_SESSION['user']['nombrePersona'] ) || $_SESSION['user']['rol'] != 1 ){
	session_destroy();
	header('Location: ../../login.php');  
}

include "../../models/mixtas.php";

$mx = new Mixtas();

$listRoles = $mx->listadoRoles();
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
											<h3 class="h6 text-uppercase mb-0">Listado de Usuarios</h3>
										</div>
										<div class="col text-right">
											<button class="btn btn-sm btn-success" data-toggle="modal" data-target="#newUserModal">Nuevo Usuario</button>
										</div>
									</div>
								</div>
								<div class="card-body">
									
									<div id="aprobacion">
										<div class="data-tables table-responsive">
											<table id="table_usuarios" class="table text-center w-100">
												<thead class="bg-gray-900 text-capitalize">
													<tr>
														<th>Opciones</th>
														<th>Nombre Completo</th>
														<th>No Documento</th>
														<th>E-mail</th>
														<th>Usuario</th>
														<th>Rol</th>
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

			<!-- Change Password Modal Start -->
			<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="labelChangePass" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="labelChangePass">Cambio de Contraseña</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form id="formChangePass">
							<div class="modal-body">
								<div class="row">
									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Nueva Contraseña</label>
											<input type="password" id="newPassword" name="newPassword" placeholder="Nombre Completo" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-6">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Confirmar Nueva Contraseña</label>
											<input type="password" id="newPasswordC" name="newPasswordC" class="form-control">
										</div>
									</div>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								<button type="submit" id="btnChangePass" class="btn btn-primary">Cambiar Contraseña</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<!-- Change Password Modal End -->

			<!-- New User Modal Start -->
			<div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="labelNewUser" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="labelNewUser">Nuevo Usuario</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form id="formNewUser">
							<div class="modal-body">
								<div class="row">
									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Nombre Completo</label>
											<input type="text" id="nombreC" name="nombreC" placeholder="Nombre Completo" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">No. de Documento</label>
											<input type="number" id="documento" name="documento" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Email</label>
											<input type="email" id="email" name="email" placeholder="E-mail" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">No. Celular</label>
											<input type="number" id="celular" name="celular" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Usuario</label>
											<input type="text" id="usuario" name="usuario" placeholder="Usuario" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Contraseña</label>
											<input type="password" id="password" name="password" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-5">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Confirmar Contraseña</label>
											<input type="password" id="passwordC" name="passwordC" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Roles</label>
											<select id="idRol" name="idRol" class="form-control">
												<option value="" selected>Seleccione un Rol</option>
												<?php foreach ($listRoles as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
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
			<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="labelEditUser" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="labelEditUser">Editar Usuario</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<form id="formEditUser">
							<div class="modal-body">
								<div class="row">
									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Nombre Completo</label>
											<input type="text" id="edit-nombreC" name="edit-nombreC" placeholder="Nombre Completo" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">No. de Documento</label>
											<input type="number" id="edit-documento" name="edit-documento" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Email</label>
											<input type="email" id="edit-email" name="edit-email" placeholder="E-mail" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">No. Celular</label>
											<input type="number" id="edit-celular" name="edit-celular" class="form-control">
										</div>
									</div>

									<div class="col-12 col-md-4">
										<div class="form-group">
											<label class="form-control-label text-uppercase">Roles</label>
											<select id="edit-idRol" name="edit-idRol" class="form-control">
												<option value="">Seleccione un Rol</option>
												<?php foreach ($listRoles as $row): ?>
													<option value="<?php echo $row['id'] ?>"><?php echo utf8_encode($row['nombre']) ?></option>
												<?php endforeach ?>
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

<script src="../../assets/js/app/administrador/usuario.js"></script>
</html>