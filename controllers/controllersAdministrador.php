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

			if (sizeof($cultivos)) {
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

			$html .= '<option value="all">Todos</option>';

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

		default:
    # code...
		break;
	}
}