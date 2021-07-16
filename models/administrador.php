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

	public function marcarVisto($id) {
		$sentencia = $this->ejecutar("UPDATE contactenos SET idVisto = 10 WHERE id = $id");

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

}

?>