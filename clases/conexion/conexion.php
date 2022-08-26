<?php



class conexion {

    private $server;
    private $user;
    private $password;
    public $conexion;


    function __construct(){
        $listadatos = $this->datosConexion();
        foreach ($listadatos as $key => $value) {
            $this->server = $value['server'];
            $this->user = $value['user'];
            $this->password = $value['password'];
        }
        $this->conexion = oci_connect($this->user,$this->password,$this->server);

        if (!$this->conexion) {
            $m = oci_error();
            trigger_error('Could not connect to database: '. $m['message'], E_USER_ERROR);
        }

    }

    //obtenemos datos de conexion desde config
    private function datosConexion(){
        $direccion = dirname(__FILE__);
        $jsondata = file_get_contents($direccion . "/" . "config");
        return json_decode($jsondata, true);
    }

    //conversion para el tipeo de datos
    private function convertirUTF8($array){
        array_walk_recursive($array,function(&$item,$key){
            //si no detecta caracter raro
            if(!mb_detect_encoding($item,'utf-8',true)){
                $item = utf8_encode($item);
            }
        });
        return $array;
    }


    public function obtenerDatos($sqlstr){
        $results = oci_parse($this->conexion, $sqlstr);
        $resultArray = array();
        if (is_array($results)) {
            foreach ($results as $d) {
                $resultArray[] = $d;
            }
        }
        return $this->convertirUTF8($resultArray);

    }

    // public function nonQuery($sqlstr){
    //     $results = oci_parse($this->conexion,$sqlstr);
        
    //     oci_execute($results);
    //     print_r($results);
    //     return oci_num_rows($results);
    // }


    //INSERT 
    public function nonQueryId($sqlstr){
        $results = oci_parse($this->conexion,$sqlstr);
        oci_execute($results);
        $filas = oci_num_rows($results);
        if($filas >= 1){
            return $this->conexion->insert_id;
        }else{
            return 0;
        }
    }
     
    //encriptar

    protected function encriptar($string){
        return md5($string);
    }


}



?>