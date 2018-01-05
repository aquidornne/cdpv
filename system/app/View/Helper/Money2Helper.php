<?php
    App::uses('AppHelper', 'View/Helper');

    class Money2Helper extends AppHelper
    {
        static public function convertDecimal($value)
        {
			$value = str_replace(".", "", $value);
			return str_replace(",", ".", $value);
		}
        static public function convertReal($value, $simboy = NULL)
        {
			return (($simboy != '') ? $simboy : '') . number_format($value, 2, ',', '.'); 
		}
	}