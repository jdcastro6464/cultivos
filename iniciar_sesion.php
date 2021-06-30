<?php

require_once __DIR__."/conf/conexion.php";
require_once __DIR__."/models/mixtas.php";

$respuesta = [
  "msj" => "Hubo un error al autenticar",
  "exito" => false
];

if( isset($_POST["documento"]) && isset($_POST["pass"]) ) {
  $documento = utf8_decode( $_POST["documento"] );
  $pass = hash("sha256", $_POST["pass"]);

  # Conexion
  $conexion = new Conexion();
  $mx = new Mixtas();

  # Buscar Persona
  $sql = "SELECT per.*, user.id as idUsu, user.usuario, user.idRol, rol.nombre as nomRol FROM __usserr__ as user
  INNER JOIN persona as per ON per.id = user.idPersona
  INNER JOIN roles as rol ON rol.id = user.idRol
  WHERE
  user.estado = 1 AND
  per.estado = 1 AND 
  user.usuario = :doc";

  $sent_user = $conexion->ejecutarConParametros($sql, [
    ":doc" => $documento
  ]);

  if( $usuario = $sent_user->fetch() ) {
    $sql .= " AND user.clave = :pass";

    $sent_pass = $conexion->ejecutarConParametros($sql, [
      ":doc" => $documento,
      ":pass" => $pass
    ]);

    if( $user = $sent_pass->fetch() ) {
      session_start();
      $_SESSION["user"] = [
        "idUsu" => $user["idUsu"],
        "idPer" => $user["id"],
        "nombreUsu" => $user["usuario"],
        "nombrePersona" => utf8_encode( $user["nombre"] ),
        "email" => utf8_encode( $user["email"] ),
        "rol" => $user["idRol"],
        "nomrol" => utf8_encode( $user["nomRol"] )
      ];

      $respuesta["exito"] = true;
      $respuesta["msj"] = "Bienvenido(a) ".$_SESSION["user"]["nombrePersona"];

      # Obtener destino rol
      $sql_rol = "SELECT * FROM roles WHERE id = ".$user["idRol"];
      $sent_rol = $conexion->ejecutar($sql_rol);

      $rol = $sent_rol->fetch();
      $respuesta["destino"] = $rol["destino"];
      $_SESSION["destino"] = $rol["destino"];
    } else {
      $respuesta["msj"] = "Nombre de Usuario o Contraseña inválidos";
    }

  } else {
    $respuesta["msj"] = "Nombre de Usuario o Contraseña inválidos";            
  }

} else {
  $respuesta["msj"] = "Campos vacíos o incorrectos";
} 

echo json_encode($respuesta);