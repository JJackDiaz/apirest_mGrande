<?php
require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';


class auth extends conexion{

    public function login($json){
      
        $_respustas = new respuestas;
        $datos = json_decode($json,true);

     

        if(!isset($datos['email']) || !isset($datos['contrasena'])){
            //error con los campos
            return $_respustas->error_400();
        }else{
            //todo esta bien 
            $email = $datos['email'];
            
            $password = $datos['contrasena'];
            $password = parent::encriptar($password);
            $datos = $this->obtenerDatosUsuario($email);

            //echo "pass: ";
            //print_r($password);
            // echo "pass: ";
            // print_r($datos[1]);

            if($datos){
                //verificar si la contraseña es igual
                    if($password == $datos[1]){
                            
                        //crear el token
                        $verificar  = $this->insertarToken($datos[0]);
                        if($verificar){
                                // si se guardo
                                $result = $_respustas->response;
                                $result["result"] = array(
                                    "token" => $verificar
                                );
                                return $result;
                        }else{
                                //error al guardar
                                return $_respustas->error_500("Error interno, No hemos podido guardar");
                        }
                            
                    }else{
                        //la contraseña no es igual
                        return $_respustas->error_200("El password es invalido");
                    }
            }else{
                //no existe el usuario
                return $_respustas->error_200("El usuario $email  no existe ");
            }
        }
    }



    private function obtenerDatosUsuario($correo){
        $query =  "SELECT id,contrasena,id_tipo_usuario FROM usuario WHERE email = '$correo'";
        $stm = oci_parse($this->conexion, $query);
        oci_execute($stm);
        $datos = oci_fetch_array($stm);
        $verifica = parent::obtenerDatos($query);
        
        //$nrow = oci_fetch_row($datos);
        // print_r( "id: ");
        // print_r($datos[0]);

        if(isset($datos[0])){
            return $datos;
        }else{
            return 0;
        }
    }


    private function insertarToken($usuarioid){
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(12,$val));
        //$date = date("Y-m-d");
        $estado = "Activo";
        $query = "INSERT INTO usuarios_token (usuarioid,token,estado)VALUES('$usuarioid','$token','$estado')";

        $stmt = oci_parse($this->conexion, $query);
        oci_execute($stmt);

        if($stmt){
            return $token;
        }else{
            return 0;
        }
    }

}




?>