<?php  //version my104
header("Content-Type: application/octet-stream");

error_reporting(0);
set_time_limit(0);
set_magic_quotes_runtime(0);

function phpversion_int()
{
    list($maVer, $miVer, $edVer) = split("[/.-]", phpversion());
    return $maVer*10000 + $miVer*100 + $edVer;
}

function CheckFunctions()
{
    if (!function_exists("mysql_connect"))
        return "mysql_connect";
    return "";
}

function GetLongBinary($num)
{
    return pack("N",$num);
}

function GetShortBinary($num)
{
    return pack("n",$num);
}

function GetDummy($count)
{
    $str = "";
    for($i=0;$i<$count;$i++)
        $str .= "\x00";
    return $str;
}

function GetBlock($val)
{
    $len = strlen($val);
    if( $len < 254 )
        return chr($len).$val;
    else
        return "\xFE".GetLongBinary($len).$val;
}

function EchoHeader($errno)
{
    $str = GetLongBinary(1111);
    $str .= GetShortBinary(104);
    $str .= GetLongBinary($errno);
    $str .= GetDummy(6);
    echo $str;
}

function EchoConnInfo($conn)
{
    $str = GetBlock(mysql_get_host_info($conn));
    $str .= GetBlock(mysql_get_proto_info($conn));
    $str .= GetBlock(mysql_get_server_info($conn));
    echo $str;
}

function EchoResultSetHeader($errno, $affectrows, $insertid, $numfields, $numrows)
{
    $str = GetLongBinary($errno);
    $str .= GetLongBinary($affectrows);
    $str .= GetLongBinary($insertid);
    $str .= GetLongBinary($numfields);
    $str .= GetLongBinary($numrows);
    $str .= GetDummy(12);
    echo $str;
}

function EchoFieldsHeader($res, $numfields)
{
    $str = "";
    for( $i = 0; $i < $numfields; $i++ ) {
        $str .= GetBlock(mysql_field_name($res, $i));
        $str .= GetBlock(mysql_field_table($res, $i));

        $type = mysql_field_type($res, $i);
        $length = mysql_field_len($res, $i);
        switch ($type) {
            case "int":
                if( $length > 11 ) $type = 8;
                elseif( $length > 9 ) $type = 3;
                elseif( $length > 6 ) $type = 9;
                elseif( $length > 4 ) $type = 2;
                else $type = 1;
                break;
            case "real":
                if( $length == 12 ) $type = 4;
                elseif( $length == 22 ) $type = 5;
                else $type = 0;
                break;
            case "null":
                $type = 6;
                break;
            case "timestamp":
                $type = 7;
                break;
            case "date":
                $type = 10;
                break;
            case "time":
                $type = 11;
                break;
            case "datetime":
                $type = 12;
                break;
            case "year":
                $type = 13;
                break;
            case "blob":
                if( $length > 16777215 ) $type = 251;
                elseif( $length > 65535 ) $type = 250;
                elseif( $length > 255 ) $type = 252;
                else $type = 249;
                break;
            default:
                $type = 253;
        }
        $str .= GetLongBinary($type);

        $flags = explode( " ", mysql_field_flags ( $res, $i ) );
        $intflag = 0;
        if(in_array( "not_null", $flags )) $intflag += 1;
        if(in_array( "primary_key", $flags )) $intflag += 2;
        if(in_array( "unique_key", $flags )) $intflag += 4;
        if(in_array( "multiple_key", $flags )) $intflag += 8;
        if(in_array( "blob", $flags )) $intflag += 16;
        if(in_array( "unsigned", $flags )) $intflag += 32;
        if(in_array( "zerofill", $flags )) $intflag += 64;
        if(in_array( "binary", $flags)) $intflag += 128;
        if(in_array( "enum", $flags )) $intflag += 256;
        if(in_array( "auto_increment", $flags )) $intflag += 512;
        if(in_array( "timestamp", $flags )) $intflag += 1024;
        if(in_array( "set", $flags )) $intflag += 2048;
        $str .= GetLongBinary($intflag);

        $str .= GetLongBinary($length);
    }
    echo $str;
}

function EchoData($res, $numfields, $numrows)
{
    for( $i = 0; $i < $numrows; $i++ ) {
        $str = "";
        $row = mysql_fetch_row( $res );
        for( $j = 0; $j < $numfields; $j++ ){
            if( is_null($row[$j]) )
                $str .= "\xFF";
            else
                $str .= GetBlock($row[$j]);
        }
        echo $str;
    }
}

    if (phpversion_int() < 40005) {
        EchoHeader(201);
        echo GetBlock("unsupported php version");
        exit();
    }

    if (phpversion_int() < 40010) {
        global $HTTP_POST_VARS;
        $_POST = &$HTTP_POST_VARS;    
    }

    if (!isset($_POST["actn"]) || !isset($_POST["host"]) || !isset($_POST["port"]) || !isset($_POST["login"])) {
        EchoHeader(202);
        echo GetBlock("invalid parameters");
        exit();
    }

    $strCheckFunctions = CheckFunctions();
    if (strlen($strCheckFunctions) > 0) {
        EchoHeader(203);
        echo GetBlock("function not exist: ".$strCheckFunctions);
        exit();
    }

    $errno_c = 0;
    $hs = $_POST["host"];
    if( $_POST["port"] ) $hs .= ":".$_POST["port"];
    $conn = mysql_connect($hs, $_POST["login"], $_POST["password"]);
    $errno_c = mysql_errno();
    if(($errno_c <= 0) && ( $_POST["db"] != "" )) {
        $res = mysql_select_db( $_POST["db"], $conn);
        $errno_c = mysql_errno();
    }

    EchoHeader($errno_c);
    if($errno_c > 0) {
        echo GetBlock(mysql_error());
    } elseif($_POST["actn"] == "C") {
        EchoConnInfo($conn);
    } elseif($_POST["actn"] == "Q") {
        for($i=0;$i<count($_POST["q"]);$i++) {
            $query = $_POST["q"][$i];
            if($query == "") continue;
            if(get_magic_quotes_gpc())
                $query = stripslashes($query);
            $res = mysql_query($query, $conn);
            $errno = mysql_errno();
            $affectedrows = mysql_affected_rows($conn);
            $insertid = mysql_insert_id($conn);
            $numfields = mysql_num_fields($res);
            $numrows = mysql_num_rows($res);
            EchoResultSetHeader($errno, $affectedrows, $insertid, $numfields, $numrows);
            if($errno > 0)
                echo GetBlock(mysql_error());
            else {
                if($numfields > 0) {
                    EchoFieldsHeader($res, $numfields);
                    EchoData($res, $numfields, $numrows);
                } else {
                    if(phpversion_int() >= 40300)
                        echo GetBlock(mysql_info($conn));
                    else
                        echo GetBlock("");
                }
            }
            if($i<(count($_POST["q"])-1))
                echo "\x01";
            else
                echo "\x00";
            mysql_free_result($res);
        }
    }

?>