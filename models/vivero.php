<?php 
include_once __DIR__."/../conf/conexion.php";

class Vivero extends Conexion{

	private $listado;

	function __construct() {
		parent::__construct();
	}	

	public function listadoRegistros($idPersona) {
		$sentencia = $this->ejecutar("SELECT reg.*, zon.nombreLugar, cul.nombre as nomCultivo, var.nombre as nomVariable FROM registros_cultivos as reg
			INNER JOIN zona_cultivos as zonC ON zonC.id = reg.idZonacultivo
			INNER JOIN zonas as zon ON zon.id = zonC.idZona
			INNER JOIN cultivos as cul ON cul.id = zonC.idCultivo
			INNER JOIN subitem as var ON var.id = reg.idVariable
			WHERE
			reg.estado = 1 AND
			zon.idPersona = $idPersona");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

	public function listadoRegistrosPredi($idPersona) {
		$sentencia = $this->ejecutar("SELECT reg.*, zon.nombreLugar, cul.nombre as nomCultivo FROM registros_cultivos_prediccion as reg
			INNER JOIN zona_cultivos as zonC ON zonC.id = reg.idZonacultivo
			INNER JOIN cultivos as cul ON cul.id = zonC.idCultivo
			INNER JOIN zonas as zon ON zon.id = zonC.idZona
			WHERE
			reg.estado = 1 AND
			zon.idPersona = $idPersona");
		$this->listado = $sentencia->fetchAll(PDO::FETCH_ASSOC);

		return $this->listado;
	}

}

?>