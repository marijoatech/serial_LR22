<?php
    define("TIPO", "IndicadorLR22");// YaouaA12
    $callback ='mycallback';
    
    
    
    if(isset($_GET['mycallback']))  {
        $callback = $_GET['mycallback'];
    }
    $arr_peso =array();


if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	/**
	 * Intenta abrir y leer el puerto, 
	 * Si no puede abrir el puerto devuelve -1000 
	 * */
	 $COM = 'COM1';
	 $puerto_com = shell_exec('powershell Get-ItemProperty -Path "HKLM:\hardware\devicemap\serialcomm"');
	 //$COM = substr($puerto_com, strpos($puerto_com, "COM"),4);
	 $raw_res = shell_exec('powershell $port= new-Object System.IO.Ports.SerialPort '.$COM.',1200,None,8,one;Try{$port.Open()}Catch{echo "-1000";$port = 0}Finally{if ($port -ne 0){for ($i=1; $i -le 5; $i++){$port.ReadLine()}$port.Close()}};');

	$resultado = explode("\n", $raw_res);

	/* $current = file_get_contents("log.txt");
	$current .=implode(" ",$resultado)."\n";

	file_put_contents("log.txt",$current); */
	//Si hubo un error al intentar leer el puerto devuelve una estado inestable con un peso de -1000
	if($resultado[0] === "-1000"){
		$arr_peso['estado']="estable";
		$arr_peso['peso']=17.630;
	}else{
		$result = preg_replace('/\D/',"", $resultado[1]);
		//$result = preg_replace('/[^0-9.]*/',"", $resultado[1]);
		//if(strrpos($resultado[1], "KGM")){
		if(preg_match('/^D|KG/',$aux[1])){
			$arr_peso['estado']="estable";
		}else{
			$arr_peso['estado']="inestable";
		}
		//$arr_peso['peso']=$result;
		$arr_peso['peso']= preg_match('/KG/',$aux[1])?preg_replace('/[^0-9.]*/',"",$aux[1]):(float)substr($result,0,3).'.'.substr($result,3,3);
	}
	echo $callback.'(' . json_encode($arr_peso) . ')';
	
} else {
	// Linux PC
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
    while($i < 2){   
	$line = $serial->readPort();   
        //print_r($line);
	if($line){
	   //echo "Durante $i  $line"; 
           break;
        }
	sleep(1);
       $i++;
    }
     $serial->deviceClose();
    
     
    $arr = explode("\n", $line);
      
    $array = array_filter($arr);
    
         
    $peso = ltrim(str_replace("=","",$array[2]), "0");  
    $peso_no_kg =  str_replace("(kg)","",$peso); 
    
    
    
    // Tomo el 2do dato que es el dato generalmente mas estable  y extraigo la basura
    // Reseteo las keys 
    $arr_peso['peso']=$peso_no_kg;
    $arr_peso['estado']="estable";

 
    echo $callback.'(' . json_encode($arr_peso) . ')';

    // If you want to change the configuration, the device must be closed.
    $serial->deviceClose();
}
?>
