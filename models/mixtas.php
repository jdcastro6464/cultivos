<?php 
include_once __DIR__."/../conf/conexion.php";

class Mixtas extends Conexion{
	
	private $listado;
	
	function __construct() {
		parent::__construct();
	}

	public function listadoentidades() {
		$sentencia = $this->ejecutar("SELECT zon.*, dep.nombre as nomDepart, mun.nombre as nomCiudad FROM zonas as zon
			INNER JOIN departamentos as dep ON dep.id = zon.idDepartamento
			INNER JOIN municipios as mun ON mun.id = zon.idCiudad
			WHERE
			zon.estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoVariables() {
		$sentencia = $this->ejecutar("SELECT * FROM subitem
			WHERE
			idItem = 3 AND
			id != 23");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoCultivos() {
		$sentencia = $this->ejecutar("SELECT * FROM cultivos
			WHERE
			estado = 1");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function selectAnio() {
		$init = 2018;
		$anio = date("Y");
		$optiones = '';
		for ($anio; $anio >= $init; $anio--) { 
			$optiones .= '<option value="'.$anio.'">'.$anio.'</option>';
		}

		return $optiones;
	}
	
}

?>