<?php
App::uses('AppHelper', 'View/Helper');

class TextXHelper extends AppHelper
{
    public function splitCamelCaseIntoWords($str)
    {
        $matches = array();
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $str, $matches);

        if (empty($matches) || count($matches) == 0) {
            echo $str;
            return;
        }

        foreach ($matches[0] as $match) {
            echo $match . ' ';
        }
    }
}