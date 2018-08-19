<?php

require __DIR__ . '/vendor/mike42/escpos-php/autoload.php';

use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;

//On Linux, your printer device file will be somewhere like /dev/lp0 (parallel)
// /dev/usb/lp1 (USB), /dev/ttyUSB0 (USB-Serial), /dev/ttyS0 (serial).
//Version filaire USB linux
$connector = new FilePrintConnector("/dev/usb/lp0");

//Version sans-fil ( enfin je crois je ne suis pas un expert non plus, mon domaine à moi c'est les tiroirs caisse)
//$connector = new FilePrintConnector("php://stdout");
$printer = new Printer($connector);
$printer -> text("Hello World!\n");
$printer -> cut();
$printer -> pulse();
$printer -> close();

//On Linux, use the usblp module to make your printer available as a device file. This is generally the default behaviour if you don't install any vendor drivers.
 //Once this is done, use a FilePrintConnector to open the device.
 // Troubleshooting: On Debian, you must be in the lp group to access this file.
 // dmesg to see what happens when you plug in your printer to make sure no other drivers are unloading the module.
