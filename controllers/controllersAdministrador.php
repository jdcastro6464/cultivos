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

require_once __DIR__."/../models/administrador.php";
require_once __DIR__."/../models/mixtas.php";

class controllersAdministrador extends Administrador {

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
	$ca = new controllersAdministrador();
	$mx = new Mixtas();
	$respuesta = [
		"exito" => false,
		"msj" => "Hubo un error al procesar la petición"
	];

	switch($peticion) {

		case 'agregarContactame':
		$paramsContactarme = [
			":nombre" => utf8_decode( $_POST['name'] ),
			":correo" => utf8_decode( $_POST['correo'] ),
			":mensaje" => utf8_decode( $_POST['message'] ),
		];

		$queryContactame = $ca->agregarContactar($paramsContactarme);

		if ($queryContactame) {
			$respuesta = [
				"exito" => true,
				"msj" => "Haremos Todo lo que esté a nuestro alcance, para contestarle lo más pronto posible"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Por favor contactar a Soporte Técnico"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'consultaHistorico':
		if ($_POST['cultivo'] == "all") {
			
			$cultivos = $ca->consultarCultivos($_POST['entidad']);
			
			if (sizeof($cultivos)) {
				$global_series = [];
				$conteo = 0;
				foreach ($cultivos as $row) {
					$variables_unicas = $ca->consultarUnicasVariables($row['id']);
					
					if (sizeof($variables_unicas)) {
						$info_series = [
							"title" => utf8_encode($row['nomCultivo']),
							"datas" => []
						];

						foreach ($variables_unicas as $key) {
							$nomVariable = $ca->nombreVariable($key['idVariable']);
							$nomMedida = $ca->traerMedida($key['idVariable']);

							$data = [
								"name" => utf8_encode($nomVariable[0]['nombre']." [".$nomMedida[0]['nombre']."]"),
								"data" => []
							];

							$reg_cul = $ca->registrosporCultivo($row['id'],$key['idVariable'],$_POST['anio']);

							foreach ($reg_cul as $reg) {
								if ($_POST['tbusqueda'] == 1) {
									$data["data"][] = [ date("Y-m",strtotime($reg['fecha_corta'])), number_format((float)$reg['valor'], 2, '.', '') ];
								} elseif ($_POST['tbusqueda'] == 2) {
									$data["data"][] = [ $reg['fecha_corta'], number_format((float)$reg['valor'], 2, '.', '') ];
								}
							}

							$info_series["datas"][] = $data;
						}		

						$global_series[] = $info_series;
						$conteo++;
					}
				}

				if ($conteo) {
					$respuesta = [
						"exito" => true,
						"info" => true,
						"msj" => $global_series
					];
				} else {
					$respuesta = [
						"exito" => true,
						"info" => false,
						"msj" => '<div class="col-12"><div class="alert alert-warning" role="alert"><h4 class="mb-0 font-weight-normal">Aún no hay registros de los cultivos de la entidad.</h4></div></div>'
					];
				}
			} else {
				$respuesta = [
					"exito" => true,
					"info" => false,
					"msj" => '<div class="col-12"><div class="alert alert-warning" role="alert"><h4 class="mb-0 font-weight-normal">No registran cultivos en la entidad seleccionada.</h4></div></div>'
				];
			}

		} else {
			
			$cultivos = $_POST['cultivo'];
			$info_cultivo = $ca->consultaonlyCultivo($cultivos);

			if (sizeof($info_cultivo)) {
				$global_series = [];
				$conteo = 0;
				$variables_unicas = $ca->consultarUnicasVariables($cultivos);

				if (sizeof($variables_unicas)) {
					$info_series = [
						"title" => utf8_encode($info_cultivo[0]['nomCultivo']),
						"datas" => []
					];

					foreach ($variables_unicas as $key) {
						$nomVariable = $ca->nombreVariable($key['idVariable']);
						$nomMedida = $ca->traerMedida($key['idVariable']);

						$data = [
							"name" => utf8_encode($nomVariable[0]['nombre']." [".$nomMedida[0]['nombre']."]"),
							"data" => []
						];

						$reg_cul = $ca->registrosporCultivo($cultivos,$key['idVariable'],$_POST['anio']);

						foreach ($reg_cul as $reg) {
							if ($_POST['tbusqueda'] == 1) {
								$data["data"][] = [ date("Y-m",strtotime($reg['fecha_corta'])), number_format((float)$reg['valor'], 2, '.', '') ];
							} elseif ($_POST['tbusqueda'] == 2) {
								$data["data"][] = [ $reg['fecha_corta'], number_format((float)$reg['valor'], 2, '.', '') ];
							}
						}

						$info_series["datas"][] = $data;
					}		

					$global_series[] = $info_series;
					$conteo++;
				}

				if ($conteo) {
					$respuesta = [
						"exito" => true,
						"info" => true,
						"msj" => $global_series
					];
				} else {
					$respuesta = [
						"exito" => true,
						"info" => false,
						"msj" => '<div class="col-12"><div class="alert alert-warning" role="alert"><h4 class="mb-0 font-weight-normal">Aún no hay registros de los cultivos de la entidad.</h4></div></div>'
					];
				}
			} else {
				$respuesta = [
					"exito" => true,
					"info" => false,
					"msj" => '<div class="col-12"><div class="alert alert-warning" role="alert"><h4 class="mb-0 font-weight-normal">No registran cultivos en la entidad seleccionada.</h4></div></div>'
				];
			}
		}		

		echo json_encode($respuesta);
		break;

		case 'consultaCultivos':
		$cultivos = $ca->consultarCultivos($_POST['entidad']);

		if (sizeof($cultivos)) {
			$html = '<option value="0" selected>Seleccione un cultivo</option>';

			foreach ($cultivos as $row) {
				$html .= '<option value="'.$row['id'].'">'.utf8_encode($row['nomCultivo']).'</option>';
			}

			if ($_POST['all']) {
				$html .= '<option value="all">Todos</option>';
			}			

			$respuesta = [
				"exito" => true,
				"msj" => "Correcto",
				"html" => $html
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Sin cultivos registrados",
				"html" => '<option value="0" selected>Seleccione un cultivo</option>'
			];
		}

		echo json_encode($respuesta);
		break;

		case 'consultaTablas':
		$paramszc = [
			":idZona" => $_POST['entidad'],
			":idCultivo" => $_POST['cultivo'],
		];

		$dzculti = $ca->zonacultivobycultivo($paramszc);
		$ref = $ca->consultaReferencia($_POST['cultivo']);
		$reg = $ca->registroTomadecision($dzculti[0]['id']);

		$ruta = "../plantilla/operaciones.xlsx";
		$libro = IOFactory::load($ruta);

		$libro->setActiveSheetIndex(0);
		$general = $libro->getActiveSheet();

		$general->setCellValue('C9', $reg[0]['valorNitrogeno']);
		$general->setCellValue('C10', $reg[0]['valorFosforo']);
		$general->setCellValue('C11', $reg[0]['valorPotasio']);
		$general->setCellValue('C12', $reg[0]['valorAcidez']);

		$neutralizar = floatval($ref[0]['maximo']) - floatval($reg[0]['valorAcidez']);
		$general->setCellValue('E12', $neutralizar);

		$html = '<div class="col-12">
		<div class="table-responsive">
		<table class="table table-bordered text-center">
		<thead>
		<tr>
		<th scope="col" colspan="5">Dosificación de Enmienda o encalante (Ton - Bultos)</th>
		</tr>
		<tr>
		<th scope="col">Fuentes</th>
		<th scope="col">Neutralización</th>
		<th scope="col">Formula QCA</th>
		<th scope="col">Dosis del Encalante (Ton)</th>
		<th scope="col">Dosis del Encalante (Bultosx50 Kg)</th>
		</tr>
		</thead>
		<tbody>';

		for ($i = 17; $i < 26; $i++) { 
			$html .= '<tr>';
			$html.= '<td>'.$general->getCell("B".$i)->getCalculatedValue().'</td>';
			$html.= '<td>'.$general->getCell("C".$i)->getCalculatedValue().'</td>';
			$html.= '<td>'.$general->getCell("D".$i)->getCalculatedValue().'</td>';
			$html.= '<td>'.$general->getCell("E".$i)->getCalculatedValue().'</td>';
			$html.= '<td>'.$general->getCell("F".$i)->getCalculatedValue().'</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody>
		</table>
		</div>
		</div>';

		switch ($_POST['cultivo']) {
			//Yuca
			case '1':
			$libro->setActiveSheetIndex(1);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Mango
			case '2':
			$libro->setActiveSheetIndex(5);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="11">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="6">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 185 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(2-4 Años)</th>
			<th scope="col">Edad<br>(5-10 Años)</th>
			<th scope="col">Edad<br>(10-15 Años)</th>
			<th scope="col">Edad<br>(15-20 Años)</th>
			<th scope="col">Edad<br>(más 20 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="11">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="6">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 185 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(2-4 Años)</th>
			<th scope="col">Edad<br>(5-10 Años)</th>
			<th scope="col">Edad<br>(10-15 Años)</th>
			<th scope="col">Edad<br>(15-20 Años)</th>
			<th scope="col">Edad<br>(más 20 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="6">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 185 árboles/ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(2-4 Años)</th>
			<th scope="col">Edad<br>(5-10 Años)</th>
			<th scope="col">Edad<br>(10-15 Años)</th>
			<th scope="col">Edad<br>(15-20 Años)</th>
			<th scope="col">Edad<br>(más 20 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Maiz
			case '3':
			$libro->setActiveSheetIndex(2);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Etapa VE<br>(0-4 días)</th>
			<th scope="col">Etapa V3<br>(25-30 días)</th>
			<th scope="col">Etapa V6<br>(55-60 días)</th>
			<th scope="col">Etapa V9<br>(75-80 días)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Etapa VE<br>(0-4 días)</th>
			<th scope="col">Etapa V3<br>(25-30 días)</th>
			<th scope="col">Etapa V6<br>(55-60 días)</th>
			<th scope="col">Etapa V9<br>(75-80 días)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Etapa VE<br>(0-4 días)</th>
			<th scope="col">Etapa V3<br>(25-30 días)</th>
			<th scope="col">Etapa V6<br>(55-60 días)</th>
			<th scope="col">Etapa V9<br>(75-80 días)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Limón
			case '4':
			$libro->setActiveSheetIndex(8);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="14">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="14">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="15">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("P".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Papaya
			case '5':
			$libro->setActiveSheetIndex(9);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="5">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Año 1</th>
			<th scope="col">Año 2</th>
			<th scope="col">Año 3</th>
			<th scope="col">Año 4</th>
			<th scope="col">Año 5</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="5">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Año 1</th>
			<th scope="col">Año 2</th>
			<th scope="col">Año 3</th>
			<th scope="col">Año 4</th>
			<th scope="col">Año 5</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="11">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="5">Distribución de la fertilización (Kg/Ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Año 1</th>
			<th scope="col">Año 2</th>
			<th scope="col">Año 3</th>
			<th scope="col">Año 4</th>
			<th scope="col">Año 5</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Guayaba
			case '6':
			$libro->setActiveSheetIndex(6);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="13">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Guandul
			case '7':
			$libro->setActiveSheetIndex(10);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">SIEMBRA</th>
			<th scope="col">30 DDS</th>
			<th scope="col">60 DDS</th>
			<th scope="col">90 DDS</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Ñame
			case '8':
			$libro->setActiveSheetIndex(3);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Etapa 1 Plantula (0-20 DDS)</th>
			<th scope="col">Etapa 2 D. vegetativo (20-90 DDS)</th>
			<th scope="col">Etapa 3 tuberización (90-210 DDS)</th>
			<th scope="col">Etapa 4 Senescencia (210-270 DDS)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="9">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			</tr>
			<tr>
			<th scope="col">Etapa 1 Plantula (0-20 DDS)</th>
			<th scope="col">Etapa 2 D. vegetativo (20-90 DDS)</th>
			<th scope="col">Etapa 3 tuberización (90-210 DDS)</th>
			<th scope="col">Etapa 4 Senescencia (210-270 DDS)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="10">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="4">Distribución de la fertilización (Kg/Ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Etapa 1 Plantula (0-20 DDS)</th>
			<th scope="col">Etapa 2 D. vegetativo (20-90 DDS)</th>
			<th scope="col">Etapa 3 tuberización (90-210 DDS)</th>
			<th scope="col">Etapa 4 Senescencia (210-270 DDS)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Marañon
			case '9':
			$libro->setActiveSheetIndex(11);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="13">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Ciruela
			case '10':
			$libro->setActiveSheetIndex(12);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="12">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="13">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="7">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 625 árboles/ha)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(7-8 Años)</th>
			<th scope="col">Edad<br>(9 -10 Años)</th>
			<th scope="col">Edad<br>(más 11 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			//Lima Tahiti
			case '11':
			$libro->setActiveSheetIndex(8);
			$cultivo = $libro->getActiveSheet();

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="14">Dosis de Nitrogeno (N)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de N (%)</th>
			<th scope="col" rowspan="2">Nitrogeno Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Nitrogeno a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">N Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 6; $i < 10; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="14">Dosis de Fósforo (P)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de P2O5 (%)</th>
			<th scope="col" rowspan="2">Fósforo Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Fósforo a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">P205 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 15; $i < 20; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';

			$html .= '<div class="col-12 mt-4">
			<div class="table-responsive">
			<table class="table table-bordered text-center">
			<thead>
			<tr>
			<th scope="col" colspan="15">Dosis de Potasio (K)</th>
			</tr>
			<tr>
			<th scope="col" rowspan="2">Fuentes</th>
			<th scope="col" rowspan="2">Contenido de K2O (%)</th>
			<th scope="col" rowspan="2">Potasio Requerido x Cultivo (KG/Ha)</th>
			<th scope="col" rowspan="2">Potasio a aportar al suelo(Kg/ha)</th>
			<th scope="col" rowspan="2">K20 Aplicación x Fuentes (Kg/ha)</th>
			<th scope="col" colspan="9">Distribución de la fertilización (Kg/plantas/Año); (Densidad de siembra de 334 árboles/ha) (Fraccionar 4 dosis/año)</th>
			<th scope="col" rowspan="2">Contenido de N Adicional (13%)</th>
			</tr>
			<tr>
			<th scope="col">Edad<br>(0-1 Años)</th>
			<th scope="col">Edad<br>(1-2 Años)</th>
			<th scope="col">Edad<br>(2-3 Años)</th>
			<th scope="col">Edad<br>(3-4 Años)</th>
			<th scope="col">Edad<br>(4-5 Años)</th>
			<th scope="col">Edad<br>(5-6 Años)</th>
			<th scope="col">Edad<br>(6-7 Años)</th>
			<th scope="col">Edad<br>(7 -8 Años)</th>
			<th scope="col">Edad<br>(8-9 Años)</th>
			</tr>
			</thead>
			<tbody>';

			for ($i = 25; $i < 28; $i++) { 
				$html .= '<tr>';
				$html.= '<td>'.$cultivo->getCell("B".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("C".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("D".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("E".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("F".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("G".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("H".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("I".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("J".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("K".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("L".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("M".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("N".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("O".$i)->getCalculatedValue().'</td>';
				$html.= '<td>'.$cultivo->getCell("P".$i)->getCalculatedValue().'</td>';
				$html .= '</tr>';
			}

			$html .= '</tbody>
			</table>
			</div>
			</div>';
			break;
			
			default:
				// code...
			break;
		}

		$respuesta = [
			"exito" => true,
			"msj" => "Correcto",
			"html" => $html
		];

		echo json_encode($respuesta);
		break;

		case 'prediccion_file':

		$ruta = "../tmp/plantilla_prediccion.xlsx";
		$rutacsv = "../tmp/plantilla_prediccion.csv";
		$info = $ca->registroPorCultivo($_POST['idCultivo']);

		if (sizeof($info)) {
			if (file_exists($ruta)) {
				unlink($ruta);
				unlink($rutacsv);

				$libro = new Spreadsheet();
				$hoja1 = $libro->getActiveSheet();
				$hoja1->setCellValue('A1', "ID");
				$hoja1->setCellValue('B1', "HUMEDAD");
				$hoja1->setCellValue('C1', "LUMINOSIDAD");
				$hoja1->setCellValue('D1', "NITROGENO");
				$hoja1->setCellValue('E1', "POTASIO");
				$hoja1->setCellValue('F1', "FOSFORO");
				$hoja1->setCellValue('G1', "ACIDEZ");
				$hoja1->setCellValue('H1', "TEMP");

				$i = 2;

				foreach ($info as $row) {
					$hoja1->setCellValue('A'.$i, $row['id']);
					$hoja1->setCellValue('B'.$i, $row['valorHumedad']);
					$hoja1->setCellValue('C'.$i, $row['valorLuminosidad']);
					$hoja1->setCellValue('D'.$i, $row['valorNitrogeno']);
					$hoja1->setCellValue('E'.$i, $row['valorPotasio']);
					$hoja1->setCellValue('F'.$i, $row['valorFosforo']);
					$hoja1->setCellValue('G'.$i, $row['valorAcidez']);
					$hoja1->setCellValue('H'.$i, $row['valorTemperatura']);

					$i++;
				}

				$writer = new Xlsx($libro);
				$writer->save("../tmp/plantilla_prediccion.xlsx");

				## Convert of Xlsc to Csv
				$reader = IOFactory::createReader("Xlsx");
				$spreadsheet = $reader->load($ruta);

				$writer = IOFactory::createWriter($spreadsheet, "Csv");
				$writer->setSheetIndex(0);
				$writer->setDelimiter(';');

				$writer->save("../tmp/plantilla_prediccion.csv");
			} else {
				$libro = new Spreadsheet();
				$hoja1 = $libro->getActiveSheet();
				$hoja1->setCellValue('A1', "ID");
				$hoja1->setCellValue('B1', "HUMEDAD");
				$hoja1->setCellValue('C1', "LUMINOSIDAD");
				$hoja1->setCellValue('D1', "NITROGENO");
				$hoja1->setCellValue('E1', "POTASIO");
				$hoja1->setCellValue('F1', "FOSFORO");
				$hoja1->setCellValue('G1', "ACIDEZ");
				$hoja1->setCellValue('H1', "TEMP");

				$i = 2;

				foreach ($info as $row) {
					$hoja1->setCellValue('A'.$i, $row['id']);
					$hoja1->setCellValue('B'.$i, $row['valorHumedad']);
					$hoja1->setCellValue('C'.$i, $row['valorLuminosidad']);
					$hoja1->setCellValue('D'.$i, $row['valorNitrogeno']);
					$hoja1->setCellValue('E'.$i, $row['valorPotasio']);
					$hoja1->setCellValue('F'.$i, $row['valorFosforo']);
					$hoja1->setCellValue('G'.$i, $row['valorAcidez']);
					$hoja1->setCellValue('H'.$i, $row['valorTemperatura']);

					$i++;
				}

				$writer = new Xlsx($libro);
				$writer->save("../tmp/plantilla_prediccion.xlsx");

				## Convert of Xlsc to Csv
				$reader = IOFactory::createReader("Xlsx");
				$spreadsheet = $reader->load($ruta);

				$writer = IOFactory::createWriter($spreadsheet, "Csv");
				$writer->setSheetIndex(0);
				$writer->setDelimiter(';');

				$writer->save("../tmp/plantilla_prediccion.csv");
			}

			$respuesta = [
				"exito" => true,
				"msj" => "Creado"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Sin registros de la variable seleccionada"
			];
		}
		

		echo json_encode($respuesta);
		break;

		case 'agregarUsuario':
		$comprobar = $ca->comprobarUsuario($_POST['usuario']);

		if ($comprobar[0]['cant']) {
			$respuesta = [
				"exito" => false,
				"msj" => "Usuario ya registra en Base de datos"
			];
		} else {
			$paramsAddPerson = [
				":nombre" => utf8_decode($_POST['nombreC']),
				":documento" => utf8_decode($_POST['documento']),
				":email" => utf8_decode($_POST['email']),
				":celular" => utf8_decode($_POST['celular']) 
			];

			$queryAddPerson = $ca->agregarPersona($paramsAddPerson);

			if ($queryAddPerson) {
				$paramsAddUser = [
					":idPersona" => $queryAddPerson ,
					":usuario" => utf8_decode($_POST['usuario']),
					":clave" => hash("sha256", utf8_decode($_POST['passwordC'])),
					":idRol" => utf8_decode($_POST['idRol']) 
				];

				$queryAddUser = $ca->agregarUsuario($paramsAddUser);

				if ($queryAddUser) {
					$respuesta = [
						"exito" => true,
						"msj" => "Usuario Agregado"
					];
				} else {
					$ca->eliminarPersona($queryAddPerson);
					$respuesta = [
						"exito" => false,
						"msj" => "Error al agregar Usuario"
					];
				}
			} else {
				$respuesta = [
					"exito" => false,
					"msj" => "Error al ingresar Persona"
				];
			}
		}

		echo json_encode($respuesta);
		break;

		case 'listarUsuarios':
		$datos = $ca->listadoUsuarios();
		$data = "";
		foreach ($datos as $lista) {
			$opciones = "<button type='button' data-idUsu='".$lista['idUsu']."' data-idPer='".$lista['id']."' class='reset btn btn-warning btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Cambiar Contraseña'><i class='fa fa-unlock'></i></button>
			<button type='button' data-idUsu='".$lista['idUsu']."' data-idPer='".$lista['id']."' class='editar btn btn-success btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Actualizar Usuario'><i class='fas fa-user-edit'></i></button>
			<button type='button' data-idUsu='".$lista['idUsu']."' data-idPer='".$lista['id']."' class='eliminar ml-2 btn btn-danger btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Eliminar Usuario'><i class='fa fa-trash'></i></button>";

			$data.= '{
				"Opciones" : "'.$ca->parse($opciones).'",
				"Nombre Completo" : "'.$ca->parse(utf8_encode($lista['nombre'])).'",
				"No Documento" : "'.$ca->parse(utf8_encode($lista['documento'])).'",
				"E-mail" : "'.$ca->parse(utf8_encode($lista['email'])).'",
				"Usuario" : "'.$ca->parse(utf8_encode($lista['usuario'])).'",
				"Rol" : "'.$ca->parse(utf8_encode($lista['nomRol'])).'"
			},';
		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'actualizarPassword':
		$paramsChangePassword = [
			":clave" => hash("sha256", utf8_decode($_POST['newPasswordC'])),
			":id" => $_POST['idUsu']
		];

		$query = $ca->cambiarContrasenia($paramsChangePassword);

		if ($query) {
			$respuesta = [
				"exito" => true,
				"msj" => "Contraseña Actualizada"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Error al actualizar contraseña"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'traerInformacionUsuario':
		$query = $ca->getInformacionUsuario($_POST['idPer']);

		if (sizeof($query)) {
			$respuesta = [
				"exito" => true,
				"nombre" => utf8_encode($query[0]['nombre']),
				"documento" => utf8_encode($query[0]['documento']),
				"email" => utf8_encode($query[0]['email']),
				"celular" => utf8_encode($query[0]['celular']),
				"idRol" => utf8_encode($query[0]['idRol']),
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Internal Error"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'actualizarUsuario':
		$paramsEditPersona = [
			":nombre" => utf8_decode($_POST['edit-nombreC']),
			":documento" => utf8_decode($_POST['edit-documento']),
			":email" => utf8_decode($_POST['edit-email']),
			":celular" => utf8_decode($_POST['edit-celular']),
			":id" => $_POST['idPer'],
		];

		$paramsEditUsuario = [
			":idRol" => $_POST['edit-idRol'],
			":id" => $_POST['idUsu'],
		];

		$queryPersona = $ca->actualizarPersona($paramsEditPersona);
		$queryUsuario = $ca->actualizarUsuario($paramsEditUsuario);

		if ($queryPersona && $queryUsuario) {
			$respuesta = [
				"exito" => true,
				"msj" => "Actualización Exitosa"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al actualizar Usuario"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'eliminarUsuario':
		$queryDeletedUsuario = $ca->deshabilitarUsuario($_POST['idUsu']);
		$queryDeletedPersona = $ca->deshabilitarPersona($_POST['idPer']);

		if ($queryDeletedPersona && $queryDeletedUsuario) {
			$respuesta = [
				"exito" => true,
				"msj" => "Eliminación Exitosa"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al eliminar Usuario"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'listarContactenos':
		$datos = $ca->listaContactenos();
		$data = "";
		foreach ($datos as $lista) {
			if ($lista['idVisto'] == 11) {
				$opciones = "<button type='button' data-idContacto='".$lista['id']."' class='visto btn btn-success mx-1' data-toggle='tooltip' data-placement='top' title='Marcar como visto'><i class='far fa-envelope'></i></button>";
			} else {
				$opciones = "<span class='text-warning py-1 px-3 border border-warning' data-toggle='tooltip' title='visto'><i class='far fa-envelope-open'></i></span>";
			}
			

			$data.= '{
				"Opciones" : "'.$ca->parse($opciones).'",
				"Nombre" : "'.$ca->parse(utf8_encode($lista['nombre'])).'",
				"E-mail" : "'.$ca->parse(utf8_encode($lista['correo'])).'",
				"Mensaje" : "'.$ca->parse(utf8_encode($lista['mensaje'])).'",
				"Fecha" : "'.$ca->parse(utf8_encode($lista['fecha'])).'"
			},';
		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'marcarComoVisto':
		$query = $ca->marcarVisto($_POST['idContacto']);

		if ($query) {
			$respuesta = [
				"exito" => true,
				"msj" => "Mensaje marcado como visto"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al marcar mensaje"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'agregarCliente':
		$paramsCliente = [
			":nombreLugar" => utf8_decode($_POST['nombre']),
			":direccion" => utf8_decode($_POST['direccion']),
			":idPersona" => utf8_decode($_POST['idPersona']),
			":idDepartamento" => utf8_decode($_POST['idDepartamento']),
			":idCiudad" => utf8_decode($_POST['idCiudad']),
		];

		$queryAddClient = $ca->agregarCliente($paramsCliente);

		if ($queryAddClient) {
			$respuesta = [
				"exito" => true,
				"msj" => "Cliente Agregado"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al agregar cliente"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'listarClientes':
		$datos = $ca->listadoClientes();
		$data = "";
		foreach ($datos as $lista) {
			$opciones = "<button type='button' data-idEnti='".$lista['id']."' class='editar btn btn-success btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Actualizar Entidad'><i class='fas fa-user-edit'></i></button>
			<button type='button' data-idEnti='".$lista['id']."' class='eliminar ml-2 btn btn-danger btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Eliminar Entidad'><i class='fa fa-trash'></i></button>";

			$data.= '{
				"Opciones" : "'.$ca->parse($opciones).'",
				"Cliente" : "'.$ca->parse(utf8_encode($lista['nomCliente'])).'",
				"Nombre" : "'.$ca->parse(utf8_encode($lista['nombreLugar'])).'",
				"Dirección" : "'.$ca->parse(utf8_encode($lista['direccion'])).'",
				"Departamento" : "'.$ca->parse(utf8_encode($lista['nomDepartamento'])).'",
				"Ciudad" : "'.$ca->parse(utf8_encode($lista['nomCiudad'])).'"
			},';
		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'consultaCuidad':
		$ciudades = $ca->buscarCiudades($_POST['idDepart']);

		if (sizeof($ciudades)) {
			$html = '<option value="" selected>Seleccione una Ciudad</option>';

			foreach ($ciudades as $row) {
				$html .= '<option value="'.$row['id'].'">'.utf8_encode($row['nombre']).'</option>';
			}

			$respuesta = [
				"exito" => true,
				"html" => $html
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "No registran ciudades"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'traerInformacionEntidad':
		$query = $ca->getInformacionEntidad($_POST['idEnti']);

		$ciudades = $ca->buscarCiudades($query[0]['idDepartamento']);

		$html = '<option value="" selected>Seleccione una Ciudad</option>';

		foreach ($ciudades as $row) {
			$html .= '<option value="'.$row['id'].'">'.utf8_encode($row['nombre']).'</option>';
		}

		if (sizeof($query)) {
			$respuesta = [
				"exito" => true,
				"idPersona" => utf8_encode($query[0]['idPersona']),
				"nombreLugar" => utf8_encode($query[0]['nombreLugar']),
				"direccion" => utf8_encode($query[0]['direccion']),
				"idDepartamento" => utf8_encode($query[0]['idDepartamento']),
				"idCiudad" => utf8_encode($query[0]['idCiudad']),
				"html" => $html,
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Internal Error"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'actualizarEntidad':
		$paramsEditEntidad = [
			":nombreLugar" => utf8_decode($_POST['edit-nombre']),
			":direccion" => utf8_decode($_POST['edit-direccion']),
			":idPersona" => utf8_decode($_POST['edit-idPersona']),
			":idDepartamento" => utf8_decode($_POST['edit-idDepartamento']),
			":idCiudad" => utf8_decode($_POST['edit-idCiudad']),
			":id" => utf8_decode($_POST['idEnti']),
		];

		$queryEntidad = $ca->actualizarEntidad($paramsEditEntidad);

		if ($queryEntidad) {
			$respuesta = [
				"exito" => true,
				"msj" => "Actualización Exitosa"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al actualizar Entidad"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'eliminarEntidad':
		$queryDeletedEntidad = $ca->deshabilitarEntidad($_POST['idEnti']);

		if ($queryDeletedEntidad) {
			$respuesta = [
				"exito" => true,
				"msj" => "Eliminación Exitosa"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al eliminar Entidad"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'eliminarCultivo':
		$queryDeletedCultivo = $ca->deshabilitarCultivo($_POST['idZonCultivo']);

		if ($queryDeletedCultivo) {
			$respuesta = [
				"exito" => true,
				"msj" => "Eliminación Exitosa"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al eliminar Cultivo"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'listarCultivos':		
		$infoCultivo = $ca->listadoCultivosEntidades($_POST['idEnti']);
		$data = "";

		foreach ($infoCultivo as $lista) {

			$opciones = "<button type='button' data-idZonaCul='".$lista['id']."' class='eliminar ml-2 btn btn-danger btn-sm mx-1' data-toggle='tooltip' data-placement='top' title='Eliminar Cultivo'><i class='fa fa-trash'></i></button>";

			$data.= '{
				"Opciones" : "'.$ca->parse($opciones).'",
				"Cultivo" : "'.$ca->parse(utf8_encode($lista['nomCultivo'])).'",
				"Hectáreas" : "'.$ca->parse(utf8_encode($lista['hectareas'])).'",
				"Metros Cuadrados" : "'.$ca->parse(utf8_encode($lista['metros_2'])).'"
			},';

		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'agregarCultivo':
		$hectareas = (isset($_POST['hectareas']) || $_POST['hectareas'] != "") ? $_POST['hectareas'] : NULL;

		$metros = (isset($_POST['metros2']) || $_POST['metros2'] != "") ? $_POST['metros2'] : NULL;

		$paramsCultivo = [
			":idZona" => $_POST['idEntidad'],
			":idCultivo" => $_POST['idCultivo'],
			":hectareas" => $hectareas,
			":metros_2" => $metros,
		];

		$queryCultivo = $ca->agregarCultivo($paramsCultivo);

		if ($queryCultivo) {
			$respuesta = [
				"exito" => true,
				"msj" => "Cultivo Agregado"
			];
		} else {
			$respuesta = [
				"exito" => false,
				"msj" => "Inconveniente al agregar cultivo"
			];
		}

		echo json_encode($respuesta);
		break;

		case 'listarRegistros':		
		$datos = $ca->listadoRegistros();
		$data = "";

		foreach ($datos as $lista) {

			$data.= '{
				"Entidad" : "'.$ca->parse(utf8_encode($lista['nombreLugar'])).'",
				"Cultivo" : "'.$ca->parse(utf8_encode($lista['nomCultivo'])).'",
				"Variable" : "'.$ca->parse(utf8_encode($lista['nomVariable'])).'",
				"Registro" : "'.$ca->parse(utf8_encode($lista['valor'])).'",
				"Fecha Larga" : "'.$ca->parse(utf8_encode($lista['fecha_larga'])).'",
				"Fecha Corta" : "'.$ca->parse(utf8_encode($lista['fecha_corta'])).'",
				"Hora" : "'.$ca->parse(utf8_encode($lista['hora'])).'"
			},';

		}

		$data = substr($data,0, strlen($data) - 1);

		echo '{"data":['.$data.']}';
		break;

		case 'listarRegistrosPrediccion':		
		$datos = $ca->listadoRegistrosPredi();
		$data = "";

		foreach ($datos as $lista) {

			$data.= '{
				"Entidad" : "'.$ca->parse(utf8_encode($lista['nombreLugar'])).'",
				"Cultivo" : "'.$ca->parse(utf8_encode($lista['nomCultivo'])).'",
				"Humedad relativa" : "'.$ca->parse(utf8_encode($lista['valorHumedad'])).'",
				"Luminosidad" : "'.$ca->parse(utf8_encode($lista['valorLuminosidad'])).'",
				"Nitrógeno (N)" : "'.$ca->parse(utf8_encode($lista['valorNitrogeno'])).'",
				"Potasio (K)" : "'.$ca->parse(utf8_encode($lista['valorPotasio'])).'",
				"Fósforo (P)" : "'.$ca->parse(utf8_encode($lista['valorFosforo'])).'",
				"Acidez del suelo (pH)" : "'.$ca->parse(utf8_encode($lista['valorAcidez'])).'",
				"Temperatura" : "'.$ca->parse(utf8_encode($lista['valorTemperatura'])).'",
				"Fecha Larga" : "'.$ca->parse(utf8_encode($lista['fecha_larga'])).'",
				"Fecha Corta" : "'.$ca->parse(utf8_encode($lista['fecha_corta'])).'",
				"Hora" : "'.$ca->parse(utf8_encode($lista['hora'])).'"
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