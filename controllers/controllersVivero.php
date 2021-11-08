<?php
session_start();
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");
ini_set('memory_limit', '-1');
set_time_limit(600);

require "../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory as IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation as DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange as NamedRange;
use \PhpOffice\PhpSpreadsheet\Style\Fill as Fill;
use \PhpOffice\PhpSpreadsheet\Style\Border as Border;
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

require_once __DIR__."/../models/vivero.php";
require_once __DIR__."/../models/mixtas.php";

class controllersVivero extends Vivero {

	public function __construct() {
		parent::__construct();
	}

	public function parse( $text ){
		$parsedText = str_replace(chr(10), "", $text);
		$parsedText = str_replace(chr(13), "", $parsedText);
		$parsedText = str_replace('"', "'", $parsedText);
		$parsedText = trim(preg_replace('/\t+/', '', $parsedText));
		return $parsedText;
	}
}

if( isset($_POST["peticion"]) || isset($_GET["peticion"]) ) {
	if (isset($_POST["peticion"])) {
		$peticion = $_POST["peticion"];
	}
	if (isset($_GET["peticion"])) {
		$peticion = $_GET["peticion"];
	}
	$cv = new controllersVivero();
	$mx = new Mixtas();
	$respuesta = [
		"exito" => false,
		"msj" => "Hubo un error al procesar la petición"
	];

	switch($peticion) {

		case 'listarRegistros':		
		$datos = $cv->listadoRegistros($_SESSION['user']['idPer']);
		$data = "";

		foreach ($datos as $lista) {

			$data.= '{
				"Entidad" : "'.$cv->parse(utf8_encode($lista['nombreLugar'])).'",
				"Cultivo" : "'.$cv->parse(utf8_encode($lista['nomCultivo'])).'",
				"Variable" : "'.$cv->parse(utf8_encode($lista['nomVariable'])).'",
				"Registro" : "'.$cv->parse(utf8_encode($lista['valor'])).'",
				"Fecha Larga" : "'.$cv->parse(utf8_encode($lista['fecha_larga'])).'",
				"Fecha Corta" : "'.$cv->parse(utf8_encode($lista['fecha_corta'])).'",
				"Hora" : "'.$cv->parse(utf8_encode($lista['hora'])).'"
			},';

		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'listarRegistrosPrediccion':		
		$datos = $cv->listadoRegistrosPredi($_SESSION['user']['idPer']);
		$data = "";

		foreach ($datos as $lista) {

			$data.= '{
				"Entidad" : "'.$cv->parse(utf8_encode($lista['nombreLugar'])).'",
				"Cultivo" : "'.$cv->parse(utf8_encode($lista['nomCultivo'])).'",
				"Humedad relativa" : "'.$cv->parse(utf8_encode($lista['valorHumedad'])).'",
				"Luminosidad" : "'.$cv->parse(utf8_encode($lista['valorLuminosidad'])).'",
				"Nitrógeno (N)" : "'.$cv->parse(utf8_encode($lista['valorNitrogeno'])).'",
				"Potasio (K)" : "'.$cv->parse(utf8_encode($lista['valorPotasio'])).'",
				"Fósforo (P)" : "'.$cv->parse(utf8_encode($lista['valorFosforo'])).'",
				"Acidez del suelo (pH)" : "'.$cv->parse(utf8_encode($lista['valorAcidez'])).'",
				"Temperatura" : "'.$cv->parse(utf8_encode($lista['valorTemperatura'])).'",
				"Fecha Larga" : "'.$cv->parse(utf8_encode($lista['fecha_larga'])).'",
				"Fecha Corta" : "'.$cv->parse(utf8_encode($lista['fecha_corta'])).'",
				"Hora" : "'.$cv->parse(utf8_encode($lista['hora'])).'"
			},';

		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		default:
			# code...
		break;
	}
}