<?php

    // Linux PC
	
	/**
	*  stty -F /dev/ttyS0 speed 1200 cs7 -parodd -cstopb  > /dev/ttyS0
	*/
	
    define("TIPO", "IndicadorLR22");// YaouaA12
    $callback ='mycallback';
    
        
    if(isset($_GET['mycallback']))  {
        $callback = $_GET['mycallback'];
    }
    $arr_peso =array();
 
    include "php_serial.class.php";

    // Let's start the class
    $serial = new phpSerial();

    // First we must specify the device. This works on both Linux and Windows (if
    // your Linux serial device is /dev/ttyS0 for COM1, etc.)
    //$serial->deviceSet("COM1");
	$serial->deviceSet("COM1");

    // Set for 9600-8-N-1 (no flow control)
    $serial->confBaudRate(1200); //Baud rate: 9600
    $serial->confParity("none");  //Parity (this is the "N" in "8-N-1")
    $serial->confCharacterLength(8); //Character length     (this is the "8" in "8-N-1")
    $serial->confStopBits(1);  //Stop bits (this is the "1" in "8-N-1")
    $serial->confFlowControl("none");

    // Then we need to open it
    $serial->deviceOpen();

    $i=0;
    $line = "";
    while($i < 6){   
	$line = $serial->readPort();   //echo $line;
	if($line){
	   //echo "Durante $i  $line",
           $serial->deviceClose();
           break;
        }
	sleep(1);
       $i++;
    }
    //echo "Despues  $line",
    $arr = explode("\n", $line);
    $array = array_filter($arr);
    $aux = array();
    // Tomo el 2do dato que es el dato generalmente mas estable  y extraigo la basura
    // Reseteo las keys 
	
	$arr_peso['peso']=0;
	$arr_peso['estado']="inestable";
	
    foreach($array as $valor){
		//print $valor."\n";
		$pos_i = strpos($valor, "KGM");
		if($pos_i > -1) { //
			$arr_peso['estado']="inestable";
			$rest = substr($valor, 3, -3);
			$arr_peso['peso']=$rest;
		}else{
			$pos_e = strpos($valor, "KG");
			if($pos_e > -1) {
				$arr_peso['estado']="estable";
				$rest = substr($valor, 3, -2);
				$arr_peso['peso']=$rest;
			}
		} 
		
        //array_push($aux,$valor);
    }
     	
    echo $callback.'(' . json_encode($arr_peso) . ')';

    // If you want to change the configuration, the device must be closed.
    $serial->deviceClose();
 
?>