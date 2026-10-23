<?php

/*
* Optimizado para python3
* Para usar con Python27  comentar la linea $py3* 
*/

$py_version = "python3";  // python27 para versiones anteriores


if(isset($_GET['mycallback']))  {
	$callback = $_GET['mycallback'];
}
if(isset($_GET['callback']))  {
	$callback = $_GET['callback'];
}
$port = $_GET['port'];
if(!isset($port)){
   $port = "ttyUSB0";
}
// $port = "ttyUSB0";

switch($_GET['action']){
	case 'get':
		$arr_mts =array();
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
			//$py = "import serial; ser = serial.Serial('/dev/$port',2400,timeout=2);v = ser.read(21);print(v);ser.close()";
			$py = "import serial; ser = serial.Serial('/dev/$port', 2400, timeout=2); v = ser.read(17); print(v.decode()); ser.close();";
			$raw_res = shell_exec(''.$py_version.' -c "'.$py.'"');
			if(strlen(trim($raw_res))>0){				
				$c = (float)str_replace(",",".",  substr($raw_res,1, strpos($raw_res,"T")-1));
				$t=  (float)str_replace(",",".", trim( substr($raw_res, strpos($raw_res,"T")+1)));
				$arr_mts['metros']=$c;
				$arr_mts['total']=$t;				
			}else{
				$arr_mts['error']="Error lectura $port, $raw_res";
			}			
			echo $callback.'('. json_encode($arr_mts).')';
		}
		break;
	case 'set':
		$response = array();
		$mts = $_GET['mts'];	
		$r = shell_exec("python27 m_setter.py '$port' $mts");	
		//$response['data'] = $r;
		echo $callback.'('. $r.')';
		break;
	case 'getPorts':
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'LIN') {
			$puertos = array();
			$py = shell_exec("python27 -m serial.tools.list_ports");
			$f_puertos = array_filter(explode("\n",$py),function($val){ if(strlen($val)>0){return true;}});
			$l_puertos = array_map(function($value){return trim(trim($value,'\/dev\/'));},$f_puertos);
			foreach($l_puertos as $p){
				if(substr($p,0,4)=='ttyS'){
					$puertos['Serial'.substr($p,-1)]=$p;
				}else{
					$puertos['USB'.substr($p,-1)]=$p;
				}
			}
			echo $callback.'('. json_encode($puertos) .')';
		}
		break;
	default:
	    echo "$callback({'error':'Opcion invalida'})";
}

?>
