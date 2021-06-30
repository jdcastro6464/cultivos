<?php
date_default_timezone_set('America/Bogota');
setLocale(LC_ALL, "es_CO");

class Conexion {
    private $datos;
    public $con;

    public function __construct() {
        include __DIR__."/datos.php";

        $this->datos = $CONEXION["desarrollo"];
    }

    public function Conectar() {
        $str_con = "mysql:host=". $this->datos["host"];
        
        if($this->datos["port"] > 0) {
            $str_con .= ":". $this->datos["port"];
        }

        $str_con .= ";dbname=". $this->datos["db"];

        try {
            $this->con = new PDO($str_con, $this->datos["user"], $this->datos["pass"]);
            $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch(Exception $e) {
            echo "<b>Hubo un error al conectar</b><br>";
        }       
    }

    public function Desconectar() {
        $this->con = null;
    }

    public function ejecutar($sql) {
        self::Conectar();

        $sentencia = $this->con->prepare($sql);
        $sentencia->execute();

        return $sentencia;
    }

    public function ejecutarConParametros($sql, $parametros) {
        self::Conectar();

        $sentencia = $this->con->prepare($sql);

        foreach ($parametros as $campo => &$valor) {
            $sentencia->bindParam($campo, $valor);
        }

        $sentencia->execute();
        # $sentencia->debugDumpParams();

        return $sentencia;
    }

    public function insertar($sql, $parametros) {
        self::Conectar();
        
        $sentencia = $this->con->prepare($sql);

        foreach ($parametros as $campo => &$valor) {
            $sentencia->bindParam($campo, $valor);
        }
        
        $sentencia->execute();
        # $sentencia->debugDumpParams();
        
        return $this->con->lastInsertId();
    }
}