<?php
App::uses('AppHelper', 'View/Helper');
App::uses('CakeEmail', 'Network/Email');
App::uses('DateHelper', 'View/Helper');
App::import('Vendor', 'wideimage/WideImage');

class ToolHelper extends AppHelper
{
    public function lastUrl()
    {
        return $this->request->referer();
    }

    public function string_rewrite($str, $utf8_decode = false)
    {
        $str = preg_replace( '/[`^~\'"]/', null, iconv( 'UTF-8', 'ASCII//TRANSLIT', $str ) );

        $purified = '';
        $length = self::strlen($str);
        if ($utf8_decode)
            $str = utf8_decode($str);
        for ($i = 0; $i < $length; $i++)
        {
            $char = self::substr($str, $i, 1);
            if (self::strlen(htmlentities($char)) > 1)
            {
                $entity = htmlentities($char, ENT_COMPAT, 'UTF-8');
                $purified .= $entity{1};
            }
            elseif (preg_match('|[[:alpha:]]{1}|u', $char))
                $purified .= $char;
            elseif (preg_match('<[[:digit:]]|-{1}>', $char))
                $purified .= $char;
            elseif ($char == ' ')
                $purified .= '-';
        }
        return trim(self::strtolower($purified));
    }

    static function strlen($str)
    {
        if (is_array($str))
            return false;
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        if (function_exists('mb_strlen'))
            return mb_strlen($str, 'utf-8');
        return strlen($str);
    }

    static function substr($str, $start, $length = false, $encoding = 'utf-8')
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_substr'))
            return mb_substr($str, intval($start), ($length === false ? self::strlen($str) : intval($length)), $encoding);
        return substr($str, $start, ($length === false ? strlen($str) : intval($length)));
    }

    static function strtolower($str)
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_strtolower'))
            return mb_strtolower($str, 'utf-8');
        return strtolower($str);
    }

    static function strtoupper($str)
    {
        if (is_array($str))
            return false;
        if (function_exists('mb_strtoupper'))
            return mb_strtoupper($str, 'utf-8');
        return strtoupper($str);
    }

    public function isAbsoluteUrl($url)
    {
        return preg_match('/(http:\/\/|https:)(www.|)/i', $url);
    }

    public function getToken()
    {
        return md5(uniqid(rand(), true));
    }

    public function genURL($path = null)
    {
        $protocolo = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === false) ? 'http://' : 'https://';
        return $protocolo . $_SERVER['HTTP_HOST'].$this->webroot.$path;
    }

    function getServerName()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_SERVER']) AND $_SERVER['HTTP_X_FORWARDED_SERVER'])
            return $_SERVER['HTTP_X_FORWARDED_SERVER'];
        return $_SERVER['SERVER_NAME'];
    }

    static public function longDate($date = false,$day_week=true,$day_week_full=true,$year=true)
    {
        if ($date) {
            $mes = date('m', strtotime($date));
        } else {
            $mes = date('m');
            $date = date('Y-m-d');
        }

        $meses = array (
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        );

        $dias = array (
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado'
        );

        return ($day_week ? $dias[date('w', strtotime($date))] : '') .( ($day_week and $day_week_full) ? ', ' : ' ' ). ($day_week_full ? date('d', strtotime($date)) . ' de ' . $meses[$mes] : '' ) .($year ? ' de ' . date('Y', strtotime($date)) : '' );
    }

    static function in_array_column($text, $column, $array)
    {
        if (!empty($array) && is_array($array)) {
            for ($i=0; $i < count($array); $i++) {
                if ($array[$i][$column]==$text || strcmp($array[$i][$column],$text)==0) return $array[$i];
            }
        }
        return false;
    }

    static function strpos_array($haystack, $needles)
    {
        $First = strlen($haystack);
        if(!is_array($needles)) $needles = array($needles);
        foreach($needles as $what) {
            $pos = strpos($haystack, $what);
            if($pos !== false)
            {
                if($pos < $First) $First = $pos;
            }
        }
        return $First == strlen($haystack) ? false : true;
    }

    static function findAndReplacePath($string)
    {
        $url_project = Router::url('/',true);

        $paths = array(
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\webroot\\files\\clients\\", 'replace' => $url_project . 'files/clients/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\banks", 'replace' => $url_project . 'files/banks/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\card_files", 'replace' => $url_project . 'files/card_files/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\client_files", 'replace' => $url_project . 'files/client_files/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\corporation", 'replace' => $url_project . 'files/corporation/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\csv_fleet", 'replace' => $url_project . 'files/csv_fleet/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\downloads", 'replace' => $url_project . 'files/downloads/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\insurers", 'replace' => $url_project . 'files/insurers/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\policy_auto_files", 'replace' => $url_project . 'files/policy_auto_files/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\policy_files", 'replace' => $url_project . 'files/policy_files/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\policy_sinister_files", 'replace' => $url_project . 'files/policy_sinister_files/'),
            array('path' => "P:\\xampp\\htdocs\\SERVICOS\\sites\\correcta_novo\\app\\webroot\\files\\uploads", 'replace' => $url_project . 'files/uploads/'),
        );

        foreach($paths as $key => $path):
            if (strpos($string, $path['path']) !== false) {
                $string = substr_replace($string, $path['replace'], strpos($string, $path['path']), strlen($path['path']));
            }
        endforeach;

        return $string;
    }

    static function findAndReplaceTag($string,$obj,$view = 'html')
    {
        $tags = Configure::read('tags');
        $url_project = Router::url('/',true);

        foreach($tags as $key => $tag) {
            if ($tag == '#LINK-NEWS#' and $view == 'html') {
                $str_replacement = '<a href="' . $url_project . 'Contents/news/' . $obj['Content']['id'] . '" target="_blank">Se n&atilde;o conseguir visualizar esta mensagem, clique aqui ou acesse a vers&atilde;o em PDF.</a>';

                $string = substr_replace($string, $str_replacement, strpos($string, $tag), strlen($tag));
            }
        }

        return $string;
    }

    public function sendMailBudget($params)
    {
        if(empty($params)){ return false; }

        $Email = new CakeEmail();
        $Email->config('branch_auto');
        $t = $Email->template('budget')
            ->subject('Orçamento - Correcta')
            ->attachments($params['path'] . $params['filename'])
            ->viewVars(array('params' => $params, 'path_images_email' => Configure::read('path-images-email')))
            ->emailFormat('html')
            ->to($params['data']['Policy']['Client']['main_email'])
            ->send();

        return $t;
    }

    /*
    public function uploadFile($uploadData, $folder, $name = '')
    {
        if ( $uploadData['size'] == 0 || $uploadData['error'] !== 0)
        {
            return array('success' => FALSE);
        }

        preg_match("/\.(png|jpg|jpeg|doc|docx|xls|xlsx|pdf){1}$/i", $uploadData["name"], $type_file);

        if(empty($type_file[1]))
        {
            return array('success' => FALSE);
        } else {
            $fileName = (($name != '') ? $this->string_rewrite($name) . "." . $type_file[1] : md5(uniqid(time())) . "." . $type_file[1]);

            $uploadFolder = 'files'. DS . $folder;
            $uploadPath =  $uploadFolder . DS . $fileName;

            if( !file_exists($uploadFolder) )
            {
                mkdir($uploadFolder);
            }

            if (move_uploaded_file($uploadData['tmp_name'], $uploadPath)) {
                return array('success' => TRUE, 'file' => $fileName, 'type_file' => $uploadData["type"]);
            } else {
                return array('success' => FALSE);
            }
        }
    }
    */

    public function uploadFile($uploadData, $folder)
    {
        if ( $uploadData['size'] == 0 || $uploadData['error'] !== 0)
        {
            return array('success' => FALSE);
        }

        preg_match("/\.(svg|png|jpg|jpeg|doc|docx|xls|xlsx|pdf){1}$/i", $uploadData["name"], $type_file);

        if(empty($type_file[1]))
        {
            return array('success' => FALSE);
        } else {
            $fileName = md5(uniqid(time())) . "." . $type_file[1];

            $uploadFolder = 'files'. DS . $folder;
            $uploadPath =  $uploadFolder . DS . $fileName;

            if( !file_exists($uploadFolder) )
            {
                mkdir($uploadFolder);
            }

            $photo = WideImage::load($uploadData['tmp_name']);
            if ($photo->getWidth() > 1000) {
                $photo = $photo->resize(1000);
                $photo->saveToFile($uploadPath);

                return array('success' => true, 'file' => $fileName, 'type_file' => $uploadData["type"]);
            }else {
                if (move_uploaded_file($uploadData['tmp_name'], $uploadPath)) {
                    return array('success' => TRUE, 'file' => $fileName, 'type_file' => $uploadData["type"]);
                } else {
                    return array('success' => FALSE);
                }
            }
            $photo->destroy();
        }
    }

    public function reArrayFiles($file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }
}