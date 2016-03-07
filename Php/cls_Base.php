<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of cls_Base
 *
 * @author root
 */
class cls_Base {
    var $BdServidor="utimpor2014"; 
    var $BdAppweb="appweb_2014"; 
    var $BdIntermedio="VSSEAINTERMEDIA"; 
    //SERVIDOR LOCAL APP SEA
    public function conexionServidor() {
        //Configuracion Local
        $bd_host = "localhost";
        $bd_usuario = "root";
        $bd_password = "root00";
        $bd_base = $this->BdServidor;
        //$bd_base = "utimpor2014";
        //$con = mysql_connect($bd_host, $bd_usuario, $bd_password) or die("Error en la conexión a MySql");
        //Creando la conexión, nuevo objeto mysqli
        $con = new mysqli($bd_host,$bd_usuario,$bd_password,$bd_base);
        //mysql_select_db($bd_base, $con);
         //Si sucede algún error la función muere e imprimir el error
        if($con->connect_error){
            die("Error en la conexion : ".$con->connect_errno."-".$con->connect_error);
        }
        //Si nada sucede retornamos la conexión
        return $con;
    }
    public function getBdServidor() {
        return $this->BdServidor;
    }
        
    //SERVIDOR REMOTO WEBAPP
    public function conexionIntermedio() {
        //Configuracion Local
        $bd_host = "localhost";
        $bd_usuario = "root";
        $bd_password = "root00";
        $bd_base = $this->BdIntermedio;
        //$con = mysql_connect($bd_host, $bd_usuario, $bd_password) or die("Error en la conexión a MySql");
        //mysql_select_db($bd_base, $con);
        $con = new mysqli($bd_host,$bd_usuario,$bd_password,$bd_base);
        if($con->connect_error){
            die("Error en la conexion : ".$con->connect_errno."-".$con->connect_error);
        }
        return $con;
    }
    public function getIntermedio() {
        return $this->BdIntermedio;
    }
    //SERVIDOR REMOTO WEBAPP
    public function conexionAppWeb() {
        //Configuracion Local
        $bd_host = "localhost";
        $bd_usuario = "root";
        $bd_password = "root00";
        $bd_base = $this->BdAppweb;
        //$con = mysql_connect($bd_host, $bd_usuario, $bd_password) or die("Error en la conexión a MySql");
        //mysql_select_db($bd_base, $con);
        $con = new mysqli($bd_host,$bd_usuario,$bd_password,$bd_base);
        if($con->connect_error){
            die("Error en la conexion : ".$con->connect_errno."-".$con->connect_error);
        }
        return $con;
    }
    public function getBdAppweb() {
        return $this->BdAppweb;
    }

}
