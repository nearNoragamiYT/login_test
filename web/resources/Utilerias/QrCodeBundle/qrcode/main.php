<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of main
 *
 * @author Miguel
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_WARNING);
include('qrlib.php');

$param = $_GET['qrcode']; // remember to sanitize that - it is user input! 
$size = $_GET['size']; // remember to sanitize that - it is user input! 
$margin = $_GET['margin']; // remember to sanitize that - it is user input! 
// we need to be sure ours script does not output anything!!! 
// otherwise it will break up PNG binary! 

ob_start("callback");

// here DB request or some processing 
$codeText = $param;

// end of processing here 
$debugLog = ob_get_contents();
ob_end_clean();

// outputs image directly into browser, as PNG stream 
QRcode::png($codeText, FALSE, QR_ECLEVEL_M, $size, $margin);
