<?php 
include_once __DIR__."/../conf/conexion.php";

class Administrador extends Conexion{
	
	private $listado;
	
	function __construct() {
		parent::__construct();
	}	

	public function agregarContactar($params) {
		$sentencia = $this->insertar("INSERT INTO contactenos(nombre, correo, mensaje) VALUES (:nombre, :correo, :mensaje)", $params);

		return $sentencia;
	}

	public function consultarCultivos($idZona) {
		$sentencia = $this->ejecutar("SELECT zonc.*, cul.nombre as nomCultivo, cul.descripcion FROM zona_cultivos as zonc
			INNER JOIN cultivos as cul ON cul.id = zonc.idCultivo
			WHERE
			zonc.idZona = $idZona AND
			zonc.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function consultarUnicasVariables($idZonacultivo) {
		$sentencia = $this->ejecutar("SELECT DISTINCT idVariable FROM registros_cultivos
			WHERE
			idZonacultivo = $idZonacultivo AND
			estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function nombreVariable($id) {
		$sentencia = $this->ejecutar("SELECT * FROM subitem
			WHERE
			id = $id");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function registrosporCultivo($idZonacultivo, $idVariable, $anio) {
		if ($anio != "all") {
			$sentencia = $this->ejecutar("SELECT * FROM registros_cultivos
				WHERE
				idZonacultivo = $idZonacultivo AND
				idVariable = $idVariable AND
				YEAR(fecha_corta) = $anio AND
				estado = 1
				ORDER BY fecha_larga ASC");
		} else {
			$sentencia = $this->ejecutar("SELECT * FROM registros_cultivos
				WHERE
				idZonacultivo = $idZonacultivo AND
				idVariable = $idVariable AND
				estado = 1
				ORDER BY fecha_larga ASC");
		}		
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function consultaonlyCultivo($id) {
		$sentencia = $this->ejecutar("SELECT zoncul.*, cul.nombre as nomCultivo, cul.descripcion FROM zona_cultivos as zoncul
			INNER JOIN cultivos as cul ON cul.id = zoncul.idCultivo
			WHERE
			zoncul.id = $id");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function traerMedida($idVariable) {
		$sentencia = $this->ejecutar("SELECT med.* FROM medida_variable as medva
			INNER JOIN subitem as med ON med.id = medva.idMedida
			WHERE
			medva.idVariable = $idVariable");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function comprobarUsuario($usuario) {
		$sentencia = $this->ejecutar("SELECT COUNT(*) as cant FROM __usserr__
			WHERE
			usuario = '$usuario'");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoUsuarios() {
		$sentencia = $this->ejecutar("SELECT per.*, usu.id as idUsu, usu.usuario, rol.nombre as nomRol FROM persona as per
			INNER JOIN __usserr__ as usu ON usu.idPersona = per.id
			INNER JOIN roles as rol ON rol.id = usu.idRol
			WHERE
			per.estado = 1 AND
			usu.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function agregarPersona($params) {
		$sentencia = $this->insertar("INSERT INTO persona(nombre, documento, email, celular) VALUES (:nombre, :documento, :email, :celular)",$params);

		return $sentencia;
	}

	public function agregarUsuario($params) {
		$sentencia = $this->insertar("INSERT INTO __usserr__(idPersona, usuario, clave, idRol) VALUES (:idPersona, :usuario, :clave, :idRol)",$params);

		return $sentencia;
	}

	public function eliminarPersona($id) {
		$sentencia = $this->ejecutar("DELETE FROM persona WHERE id = $id");

		return $sentencia;
	}

	public function deshabilitarPersona($id) {
		$sentencia = $this->ejecutar("UPDATE persona SET estado = 2 WHERE id = $id");

		return $sentencia;
	}

	public function deshabilitarUsuario($id) {
		$sentencia = $this->ejecutar("UPDATE __usserr__ SET estado = 2 WHERE id = $id");

		return $sentencia;
	}

	public function agregarCliente($params) {
		$sentencia = $this->insertar("INSERT INTO zonas(idPersona, nombreLugar, direccion, idDepartamento, idCiudad) VALUES (:idPersona, :nombreLugar, :direccion, :idDepartamento, :idCiudad)",$params);

		return $sentencia;
	}

	public function marcarVisto($id) {
		$sentencia = $this->ejecutar("UPDATE contactenos SET idVisto = 10 WHERE id = $id");

		return $sentencia;
	}

	public function deshabilitarEntidad($id) {
		$sentencia = $this->ejecutar("UPDATE zonas SET estado = 2 WHERE id = $id");

		return $sentencia;
	}

	public function deshabilitarCultivo($id) {
		$sentencia = $this->ejecutar("UPDATE zona_cultivos SET estado = 2 WHERE id = $id");

		return $sentencia;
	}

	public function cambiarContrasenia($params) {
		$sentencia = $this->ejecutarConParametros("UPDATE __usserr__ SET clave = :clave WHERE id = :id", $params);

		return $sentencia;
	}

	public function actualizarPersona($params) {
		$sentencia = $this->ejecutarConParametros("UPDATE persona SET nombre = :nombre, documento = :documento, email = :email, celular = :celular WHERE id = :id", $params);

		return $sentencia;
	}

	public function actualizarEntidad($params) {
		$sentencia = $this->ejecutarConParametros("UPDATE zonas SET idPersona = :idPersona, nombreLugar = :nombreLugar, direccion = :direccion, idDepartamento = :idDepartamento, idCiudad = :idCiudad WHERE id = :id", $params);

		return $sentencia;
	}

	public function actualizarUsuario($params) {
		$sentencia = $this->ejecutarConParametros("UPDATE __usserr__ SET idRol = :idRol WHERE id = :id", $params);

		return $sentencia;
	}

	public function registroPorCultivo($idCultivo) {
		$sentencia = $this->ejecutar("SELECT reg.* FROM registros_cultivos_prediccion as reg
			INNER JOIN zona_cultivos as zon ON zon.id = reg.idZonacultivo AND zon.idCultivo = $idCultivo");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function zonacultivobycultivo($params) {
		$sentencia = $this->ejecutarConParametros("SELECT * FROM zona_cultivos
			WHERE
			idZona = :idZona AND
			idCultivo = :idCultivo AND
			estado = 1", $params);
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function registroTomadecision($idZonacultivo) {
		$sentencia = $this->ejecutar("SELECT * FROM registros_cultivos_prediccion
			WHERE
			idZonacultivo = $idZonacultivo
			ORDER BY id DESC LIMIT 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function consultaReferencia($cultivo) {
		$sentencia = $this->ejecutar("SELECT * FROM referencia_cultivo
			WHERE
			idCultivo = $cultivo AND
			idVariable = 22");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listaContactenos() {
		$sentencia = $this->ejecutar("SELECT * FROM contactenos");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function getInformacionUsuario($idPer) {
		$sentencia = $this->ejecutar("SELECT per.*, usu.idRol FROM persona as per
			INNER JOIN __usserr__ as usu ON usu.idPersona = per.id and per.id = $idPer");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoClientes() {
		$sentencia = $this->ejecutar("SELECT zon.*, dep.nombre as nomDepartamento, mun.nombre as nomCiudad, per.nombre as nomCliente FROM zonas as zon
			INNER JOIN departamentos as dep ON dep.id = zon.idDepartamento
			INNER JOIN municipios as mun ON mun.id = zon.idCiudad
			INNER JOIN persona as per ON per.id = zon.idPersona
			WHERE
			zon.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function buscarCiudades($idDepart) {
		$sentencia = $this->ejecutar("SELECT * FROM municipios WHERE idDepart = $idDepart");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function getInformacionEntidad($idEnti) {
		$sentencia = $this->ejecutar("SELECT * FROM zonas WHERE id = $idEnti");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function agregarCultivo($params) {
		$sentencia = $this->insertar("INSERT INTO zona_cultivos(idZona, idCultivo, hectareas, metros_2) VALUES (:idZona, :idCultivo, :hectareas, :metros_2)",$params);

		return $sentencia;
	}

	public function listadoCultivosEntidades($id) {
		$sentencia = $this->ejecutar("SELECT zon.*, cul.nombre as nomCultivo FROM zona_cultivos as zon
			INNER JOIN cultivos as cul ON cul.id = zon.idCultivo
			WHERE
			zon.idZona = $id AND
			zon.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoRegistros() {
		$sentencia = $this->ejecutar("SELECT reg.*, zon.nombreLugar, cul.nombre as nomCultivo, var.nombre as nomVariable FROM registros_cultivos as reg
			INNER JOIN zona_cultivos as zonC ON zonC.id = reg.idZonacultivo
			INNER JOIN zonas as zon ON zon.id = zonC.idZona
			INNER JOIN cultivos as cul ON cul.id = zonC.idCultivo
			INNER JOIN subitem as var ON var.id = reg.idVariable
			WHERE
			reg.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoRegistrosPredi() {
		$sentencia = $this->ejecutar("SELECT reg.*, zon.nombreLugar, cul.nombre as nomCultivo FROM registros_cultivos_prediccion as reg
			INNER JOIN zona_cultivos as zonC ON zonC.id = reg.idZonacultivo
			INNER JOIN cultivos as cul ON cul.id = zonC.idCultivo
			INNER JOIN zonas as zon ON zon.id = zonC.idZona
			WHERE
			reg.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

}

?>