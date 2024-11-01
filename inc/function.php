<?php 


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}




class wpxlsdata_FUN{

public function Check_text_english($string)

{
  return preg_match('/[^A-Za-z0-9]/', $string) ;

}

}