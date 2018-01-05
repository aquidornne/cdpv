<?php

    class Tools
    {
        /**
         * Random password generator
         * @param integer $length Desired length (optional)
         * @return string Password
         */
        static public function passwordGen($length = 8)
        {
            $str = 'abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0, $password = ''; $i < $length; $i++)
                $password .= self::substr($str, mt_rand(0, self::strlen($str) - 1), 1);

            return $password;
        }

        /**
         * Redirect user to another page
         * @param string $url Desired URL
         * @param string $baseUri Base URI (optional)
         */
        static public function redirect($url = NULL)
        {
            if (!$url) {
                header('Location: ' . _project_url_);
            }else {
                header('Location: ' . ($url ? $url : _project_url_));
            }
            exit;
        }

        static public function FormataData($date, $locale = "us", $datetime = TRUE)
        {

            # Exception
            if (is_null($date))
                $date = date("m/d/Y H:i:s");

            # Let's go ahead and get a string date in case we've been given a Unix Time Stamp
            if ($locale == "unix")
                $date = date("m/d/Y H:i:s", $date);

            # Separate Date from Time
            $date = explode(" ", $date);

            if ($locale == "br") {
                # Separate d/m/Y from Date
                $date[0] = explode("/", $date[0]);
                # Rearrange Date into m/d/Y
                $date[0] = $date[0][1] . "/" . $date[0][0] . "/" . $date[0][2];
            }

            # Return date in all formats
            # US
            $Return["datetime"]["us"] = implode(" ", $date);
            $Return["date"]["us"] = $date[0];
            # Universal
            $Return["time"] = $date[1];
            $Return["unix_datetime"] = strtotime($Return["datetime"]["us"]);
            $Return["unix_date"] = strtotime($Return["date"]["us"]);
            $Return["getdate"] = getdate($Return["unix_datetime"]);
            # BR
            $Return["datetime"]["br"] = date("d-m-Y H:i:s", $Return["unix_datetime"]);
            $Return["date"]["br"] = date("d-m-Y", $Return["unix_date"]);

            if ($datetime) {
                $Return = $Return["datetime"]["br"];
            }

            return $Return;
        }

        static public function getHttpHost($http = FALSE, $entities = FALSE)
        {
            $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
            if ($entities)
                $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
            if ($http)
                $host = (_SSL_ENABLED_ ? 'https://' : 'http://') . $host;

            return $host;
        }

        static public function getCurrentUrlReality()
        {
            $protocolo = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE) ? 'http' : 'https';

            return $protocolo . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?' . $_SERVER['QUERY_STRING'];
        }

        static public function getCurrentUrl()
        {
            $protocolo = (strpos(strtolower($_SERVER['SERVER_PROTOCOL']), 'https') === FALSE) ? 'http://' : 'https://';

            return $protocolo . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }

        /**
         * Get the server variable SERVER_NAME
         *
         * @param string $referrer URL referrer
         */
        static function getServerName()
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_SERVER']) AND $_SERVER['HTTP_X_FORWARDED_SERVER'])
                return $_SERVER['HTTP_X_FORWARDED_SERVER'];

            return $_SERVER['SERVER_NAME'];
        }

        /**
         * Secure an URL referrer
         * @param string $referrer URL referrer
         */
        static public function secureReferrer($referrer)
        {
            if (preg_match('/^http[s]?:\/\/' . self::getServerName() . '\/.*$/Ui', $referrer))
                return $referrer;
            else
                return FALSE;
        }

        /**
         * Get a value from $_POST / $_GET
         * if unavailable, take a default value
         *
         * @param string $key Value key
         * @param mixed $defaultValue (optional)
         * @return mixed Value
         */
        static public function getValue($key, $defaultValue = FALSE)
        {
            if (!isset($key) OR empty($key) OR !is_string($key))
                return FALSE;
            $ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

            if (is_string($ret) === TRUE)
                $ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));

            return !is_string($ret) ? $ret : stripslashes($ret);
        }

        static public function getIsset($key)
        {
            if (!isset($key) OR empty($key) OR !is_string($key))
                return FALSE;

            return isset($_POST[$key]) ? TRUE : (isset($_GET[$key]) ? TRUE : FALSE);
        }

        /**
         * Display date regarding to language preferences
         *
         * @param string $date Date to display format UNIX
         * @param integer $id_lang Language id
         * @param boolean $full With time or not (optional)
         * @return string Date
         */
        static public function displayDate($date, $id_lang, $full = FALSE, $separator = '/')
        {
            $tmpTab = explode($separator, substr($date, 0, 10));
            $hour = ' ' . substr($date, -8);

            if ($id_lang == 'birthdate')
                return ($tmpTab[2] . '/' . $tmpTab[1]);
            elseif ($id_lang == 'us')
                return ($tmpTab[2] . '-' . $tmpTab[1] . '-' . $tmpTab[0] . ($full ? $hour : ''));
            elseif ($id_lang == 'fb')
                return ($tmpTab[2] . '-' . $tmpTab[0] . '-' . $tmpTab[1] . ($full ? $hour : ''));
            else
                return ($tmpTab[0] . '-' . $tmpTab[1] . '-' . $tmpTab[2] . ($full ? $hour : ''));
        }

        /**
         * Sanitize a string
         *
         * @param string $string String to sanitize
         * @param boolean $full String contains HTML or not (optional)
         * @return string Sanitized string
         */
        static public function safeOutput($string, $html = FALSE)
        {
            if (!$html)
                $string = @htmlentities(strip_tags($string), ENT_QUOTES, 'utf-8');

            return $string;
        }

        static public function htmlentitiesUTF8($string, $type = ENT_QUOTES)
        {
            if (is_array($string))
                return array_map(array('Tools', 'htmlentitiesUTF8'), $string);

            return htmlentities($string, $type, 'utf-8');
        }

        static public function htmlentitiesDecodeUTF8($string)
        {
            if (is_array($string))
                return array_map(array('Tools', 'htmlentitiesDecodeUTF8'), $string);

            return html_entity_decode($string, ENT_QUOTES, 'utf-8');
        }

        static public function safePostVars()
        {
            $_POST = array_map(array('Tools', 'htmlentitiesUTF8'), $_POST);
        }

        /**
         * Display an error according to an error code
         *
         * @param integer $code Error code
         */
        static public function displayError($string = 'Message error default.', $htmlentities = TRUE)
        {
            global $_ERRORS;

            //if ($string == 'tentativa de corte!') d(debug_backtrace());
            if (!is_array($_ERRORS))
                return str_replace('"', '&quot;', $string);
            $key = md5(str_replace('\'', '\\\'', $string));
            $str = (isset($_ERRORS) AND is_array($_ERRORS) AND key_exists($key, $_ERRORS)) ? ($htmlentities ? htmlentities($_ERRORS[$key], ENT_COMPAT, 'UTF-8') : $_ERRORS[$key]) : $string;

            return str_replace('"', '&quot;', stripslashes($str));
        }

        /**
         * Display an error with detailed object
         *
         * @param object $object Object to display
         */
        static public function dieObject($object, $kill = TRUE)
        {
            echo '<pre style="text-align: left;">';
            print_r($object);
            echo '</pre><br />';
            if ($kill)
                die('END');

            return ($object);
        }

        /**
         * Check if submit has been posted
         *
         * @param string $submit submit name
         */
        static public function isSubmit($submit)
        {
            return (
                isset($_POST[$submit]) OR isset($_POST[$submit . '_x']) OR isset($_POST[$submit . '_y'])
                OR isset($_GET[$submit]) OR isset($_GET[$submit . '_x']) OR isset($_GET[$submit . '_y'])
            );
        }

        /**
         * @return bool
         */
        static public function isPost()
        {
            return $_POST ? TRUE : FALSE;
        }

        /**
         * @return bool
         */
        static public function isGet()
        {
            return $_GET ? TRUE : FALSE;
        }

        /**
         * Encrypt password
         *
         * @param object $object Object to display
         */
        static public function encrypt($password)
        {
            return md5($password);
        }

        static private function crypto_rand_secure($min, $max)
        {
            $range = $max - $min;
            if ($range < 0)
                return $min; // not so random...
            $log = log($range, 2);
            $bytes = (int)($log / 8) + 1; // length in bytes
            $bits = (int)$log + 1; // length in bits
            $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);

            return $min + $rnd;
        }

        static public function getToken($length = 32)
        {
            $token = "";
            $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
            $codeAlphabet .= "0123456789";
            for ($i = 0; $i < $length; $i++) {
                $token .= $codeAlphabet[Tools::crypto_rand_secure(0, strlen($codeAlphabet))];
            }

            return $token;
        }

        static public function string_rewrite($str, $utf8_decode = FALSE)
        {
            $str = preg_replace('/[`^~\'"]/', NULL, iconv('UTF-8', 'ASCII//TRANSLIT', $str));

            $purified = '';
            $length = self::strlen($str);
            if ($utf8_decode)
                $str = utf8_decode($str);
            for ($i = 0; $i < $length; $i++) {
                $char = self::substr($str, $i, 1);
                if (self::strlen(htmlentities($char)) > 1) {
                    $entity = htmlentities($char, ENT_COMPAT, 'UTF-8');
                    $purified .= $entity{1};
                } elseif (preg_match('|[[:alpha:]]{1}|u', $char))
                    $purified .= $char;
                elseif (preg_match('<[[:digit:]]|-{1}>', $char))
                    $purified .= $char;
                elseif ($char == ' ')
                    $purified .= '-';
            }

            return trim(self::strtolower($purified));
        }

        static function is_utf8($string)
        {
            return preg_match('%^(?: [x09x0Ax0Dx20-x7E] # ASCII
        | [xC2-xDF][x80-xBF] # non-overlong 2-byte
        | xE0[xA0-xBF][x80-xBF] # excluding overlongs
        | [xE1-xECxEExEF][x80-xBF]{2} # straight 3-byte
        | xED[x80-x9F][x80-xBF] # excluding surrogates
        | xF0[x90-xBF][x80-xBF]{2} # planes 1-3
        | [xF1-xF3][x80-xBF]{3} # planes 4-15
        | xF4[x80-x8F][x80-xBF]{2} # plane 16
        )*$%xs', $string);
        }

        /**
         * Truncate strings
         *
         * @param string $str
         * @param integer $maxLen Max length
         * @param string $suffix Suffix optional
         * @return string $str truncated
         */
        /* CAUTION : Use it only on module hookEvents.
        ** For other purposes use the smarty function instead */
        static public function truncate($str, $maxLen, $suffix = '...')
        {
            if (self::strlen($str) <= $maxLen)
                return $str;
            $str = utf8_decode($str);

            return (utf8_encode(substr($str, 0, $maxLen - self::strlen($suffix)) . $suffix));
        }

        /**
         * Generate date form
         *
         * @param integer $year Year to select
         * @param integer $month Month to select
         * @param integer $day Day to select
         * @return array $tab html data with 3 cells :['days'], ['months'], ['years']
         *
         */
        static public function dateYears()
        {
            for ($i = date('Y') - 10; $i >= 1900; $i--)
                $tab[] = $i;

            return $tab;
        }

        static public function dateDays()
        {
            for ($i = 1; $i != 32; $i++)
                $tab[] = $i;

            return $tab;
        }

        static public function dateMonths()
        {
            for ($i = 1; $i != 13; $i++)
                $tab[$i] = date('F', mktime(0, 0, 0, $i, date('m'), date('Y')));

            return $tab;
        }

        static public function dateMonth()
        {
            switch (date("m")) {
                case "01":
                    return 'Janeiro';
                    break;
                case "02":
                    return 'Fevereiro';
                    break;
                case "03":
                    return 'Março';
                    break;
                case "04":
                    return 'Abril';
                    break;
                case "05":
                    return 'Maio';
                    break;
                case "06":
                    return 'Junho';
                    break;
                case "07":
                    return 'Julho';
                    break;
                case "08":
                    return 'Agosto';
                    break;
                case "09":
                    return 'Setembro';
                    break;
                case "10":
                    return 'Outubro';
                    break;
                case "11":
                    return 'Novembro';
                    break;
                case "12":
                    return 'Dezembro';
                    break;
            }
        }

        static public function hourGenerate($hours, $minutes, $seconds)
        {
            return implode(':', array($hours, $minutes, $seconds));
        }

        static public function dateFrom($date)
        {
            $tab = explode(' ', $date);
            if (!isset($tab[1]))
                $date .= ' ' . self::hourGenerate(0, 0, 0);

            return $date;
        }

        static public function dateTo($date)
        {
            $tab = explode(' ', $date);
            if (!isset($tab[1]))
                $date .= ' ' . self::hourGenerate(23, 59, 59);

            return $date;
        }

        static public function getExactTime()
        {
            return time() + microtime();
        }

        static function strtolower($str)
        {
            if (is_array($str))
                return FALSE;
            if (function_exists('mb_strtolower'))
                return mb_strtolower($str, 'utf-8');

            return strtolower($str);
        }

        static function strlen($str)
        {
            if (is_array($str))
                return FALSE;
            $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
            if (function_exists('mb_strlen'))
                return mb_strlen($str, 'utf-8');

            return strlen($str);
        }

        static function strtoupper($str)
        {
            if (is_array($str))
                return FALSE;
            if (function_exists('mb_strtoupper'))
                return mb_strtoupper($str, 'utf-8');

            return strtoupper($str);
        }

        static function substr($str, $start, $length = FALSE, $encoding = 'utf-8')
        {
            if (is_array($str))
                return FALSE;
            if (function_exists('mb_substr'))
                return mb_substr($str, intval($start), ($length === FALSE ? self::strlen($str) : intval($length)), $encoding);

            return substr($str, $start, ($length === FALSE ? strlen($str) : intval($length)));
        }

        static function ucfirst($str)
        {
            return self::strtoupper(self::substr($str, 0, 1)) . self::substr($str, 1);
        }

        static public function iconv($from, $to, $string)
        {
            if (function_exists('iconv'))
                return iconv($from, $to . '//TRANSLIT', str_replace('¥', '&yen;', str_replace('£', '&pound;', str_replace('€', '&euro;', $string))));

            return html_entity_decode(htmlentities($string, ENT_NOQUOTES, $from), ENT_NOQUOTES, $to);
        }

        static public function isEmpty($field)
        {
            return $field === '' OR $field === NULL;
        }

        public function MinuteByhora($mins)
        {
            // Se os minutos estiverem negativos
            if ($mins < 0)
                $min = abs($mins);
            else
                $min = $mins;

            // Arredonda a hora
            $h = floor($min / 60);
            $m = ($min - ($h * 60)) / 100;
            $horas = $h + $m;

            // Matemática da quinta série
            // Detalhe: Aqui também pode se usar o abs()
            if ($mins < 0)
                $horas *= -1;

            // Separa a hora dos minutos
            $sep = explode('.', $horas);
            $h = $sep[0];
            if (empty($sep[1]))
                $sep[1] = 00;

            $m = $sep[1];

            // Aqui um pequeno artifício pra colocar um zero no final
            if (strlen($m) < 2)
                $m = $m . 0;

            return sprintf('%02d:%02d', $h, $m);
        }

        /**
         * return date by extensive ex. 2013-10-05 = Sábado 05 de Outubro de 2013
         * @param bool $date
         * @param bool $day_week
         * @param bool $day_week_full
         * @param bool $year
         * @return string
         */
        static public function longDate($date = FALSE, $day_week = TRUE, $day_week_full = TRUE, $year = TRUE)
        {
            if ($date) {
                $mes = date('m', strtotime($date));
            } else {
                $mes = date('m');
                $date = date('Y-m-d');
            }
            $meses = array
            (
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
            $dias = array
            (
                0 => 'Domingo',
                1 => 'Segunda-feira',
                2 => 'Terça-feira',
                3 => 'Quarta-feira',
                4 => 'Quinta-feira',
                5 => 'Sexta-feira',
                6 => 'Sábado'
            );

            return ($day_week ? $dias[date('w', strtotime($date))] : '') . (($day_week and $day_week_full) ? ', ' : ' ') . ($day_week_full ? date('d', strtotime($date)) . ' de ' . $meses[$mes] : '') . ($year ? ' de ' . date('Y', strtotime($date)) : '');
        }

        /**
         * humanized to date ex. 1 meses atrás = 2013-09-04
         * @param      $date
         * @param bool $group
         * @return string
         */
        static public function humanizeDate($date, $group = FALSE)
        {

            $timestamp = strtotime($date); #converto a data para strtotime
            $date = Tools::displayDate($date, '', FALSE, '-');

            //type cast, current time, difference in timestamps
            $timestamp = (int)$timestamp;
            $current_time = time();
            $diff = $current_time - $timestamp;

            //intervals in seconds
            $intervals = array(
                'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute' => 60
            );

            if ($group) {
                if (strtotime($date) == strtotime(Date('Y-m-d'))) {
                    return 'Hoje';
                }

                if (strtotime($date) == strtotime(Date('Y-m-d', strtotime("-1 days")))) {
                    return 'Ontem';
                }

                if ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                    return Tools::longDate(Tools::displayDate($date, '', FALSE, '-'), TRUE, FALSE, FALSE);
                }

                if ($diff >= $intervals['day'] && $diff > $intervals['week'] && date('Y', strtotime($date)) == date('Y')) {
                    return Tools::longDate(Tools::displayDate($date, '', FALSE, '-'), FALSE, TRUE, FALSE);
                }

                if (date('Y', strtotime("-1 year", strtotime(date('Y-m-d')))) < date('Y')) {
                    return Tools::longDate(Tools::displayDate($date, '', FALSE, '-'), FALSE, TRUE, TRUE);
                }
            } else {
                //now we just find the difference
                if ($diff == 0) {
                    return 'agora mesmo';
                }

                if ($diff < 60) {
                    return $diff == 1 ? $diff . ' segundo atrás' : $diff . ' segundos atrás';
                }

                if ($diff >= 60 && $diff < $intervals['hour']) {
                    $diff = floor($diff / $intervals['minute']);

                    return $diff == 1 ? $diff . ' minuto atrás' : $diff . ' minutos atrás';
                }

                if ($diff >= $intervals['hour'] && $diff < $intervals['day']) {
                    $diff = floor($diff / $intervals['hour']);

                    return $diff == 1 ? $diff . ' hora atrás' : $diff . ' horas atrás';
                }

                if ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                    $diff = floor($diff / $intervals['day']);

                    return $diff == 1 ? $diff . ' dia atrás' : $diff . ' dias atrás';
                }

                if ($diff >= $intervals['week'] && $diff < $intervals['month']) {
                    $diff = floor($diff / $intervals['week']);

                    return $diff == 1 ? $diff . ' semana atrás' : $diff . ' semanas atrás';
                }

                if ($diff >= $intervals['month'] && $diff < $intervals['year']) {
                    $diff = floor($diff / $intervals['month']);

                    return $diff == 1 ? $diff . ' meses atrás' : $diff . ' meses atrás';
                }

                if ($diff >= $intervals['year']) {
                    $diff = floor($diff / $intervals['year']);

                    return $diff == 1 ? $diff . ' ano atrás' : $diff . ' anos atrás';
                }
            }
        }

        /**
         * find needles in haystack
         * @param $haystack
         * @param $needles
         * @return bool|int
         */
        static public function strpos_array($haystack, $needles)
        {
            $First = strlen($haystack);
            if (!is_array($needles))
                $needles = array($needles);
            foreach ($needles as $what) {
                $pos = strpos($haystack, $what);
                if ($pos !== FALSE) {
                    if ($pos < $First)
                        $First = $pos;
                }
            }

            return $First == strlen($haystack) ? FALSE : TRUE;
        }

        static function strposa($haystack, $needles = array(), $offset = 0)
        {
            $chr = array();
            foreach ($needles as $needle) {
                $res = strpos($haystack, $needle, $offset);
                if ($res !== FALSE)
                    $chr[$needle] = $res;
            }
            if (empty($chr))
                return FALSE;

            return min($chr);
        }

        /**
         * partitions url in array
         * @param      $url
         * @param bool $scheme
         * @param bool $host
         * @param bool $path
         * @param bool $query
         * @return bool
         */
        static public function parserURL($url, $scheme = TRUE, $host = TRUE, $path = FALSE, $query = FALSE)
        {
            if (!empty($url)) {
                $parse = parse_url($url);

                return ($scheme ? $parse['scheme'] . '://' : '') . ($host ? $parse['host'] : '') . ($path ? $parse['path'] : '') . ($query ? $parse['query'] : '');

            } else {
                return FALSE;
            }
        }

        static public function array_sort($array, $on, $order = SORT_ASC)
        {
            $new_array = array();
            $sortable_array = array();

            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $k2 => $v2) {
                            if ($k2 == $on) {
                                $sortable_array[$k] = $v2;
                            }
                        }
                    } else {
                        $sortable_array[$k] = $v;
                    }
                }

                switch ($order) {
                    case SORT_ASC:
                        asort($sortable_array);
                        break;
                    case SORT_DESC:
                        arsort($sortable_array);
                        break;
                }

                foreach ($sortable_array as $k => $v) {
                    $new_array[$k] = $array[$k];
                }
            }

            return $new_array;
        }

        static public function array_unique_obj($obj)
        {
            static $idList = array();
            if (in_array($obj->ServiceCode, $idList)) {
                return FALSE;
            }
            $idList [] = $obj->ServiceCode;

            return TRUE;
        }

        static private function mod($dividendo, $divisor)
        {
            return round($dividendo - (floor($dividendo / $divisor) * $divisor));
        }

        static public function genCPF($compontos = FALSE)
        {
            $n1 = rand(0, 9);
            $n2 = rand(0, 9);
            $n3 = rand(0, 9);
            $n4 = rand(0, 9);
            $n5 = rand(0, 9);
            $n6 = rand(0, 9);
            $n7 = rand(0, 9);
            $n8 = rand(0, 9);
            $n9 = rand(0, 9);
            $d1 = $n9 * 2 + $n8 * 3 + $n7 * 4 + $n6 * 5 + $n5 * 6 + $n4 * 7 + $n3 * 8 + $n2 * 9 + $n1 * 10;
            $d1 = 11 - (self::mod($d1, 11));
            if ($d1 >= 10) {
                $d1 = 0;
            }
            $d2 = $d1 * 2 + $n9 * 3 + $n8 * 4 + $n7 * 5 + $n6 * 6 + $n5 * 7 + $n4 * 8 + $n3 * 9 + $n2 * 10 + $n1 * 11;
            $d2 = 11 - (self::mod($d2, 11));
            if ($d2 >= 10) {
                $d2 = 0;
            }
            $retorno = '';
            if ($compontos == 1) {
                $retorno = '' . $n1 . $n2 . $n3 . "." . $n4 . $n5 . $n6 . "." . $n7 . $n8 . $n9 . "-" . $d1 . $d2;
            } else {
                $retorno = '' . $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $d1 . $d2;
            }

            return $retorno;
        }

        static public function genCnpj($compontos)
        {
            $n1 = rand(0, 9);
            $n2 = rand(0, 9);
            $n3 = rand(0, 9);
            $n4 = rand(0, 9);
            $n5 = rand(0, 9);
            $n6 = rand(0, 9);
            $n7 = rand(0, 9);
            $n8 = rand(0, 9);
            $n9 = 0;
            $n10 = 0;
            $n11 = 0;
            $n12 = 1;
            $d1 = $n12 * 2 + $n11 * 3 + $n10 * 4 + $n9 * 5 + $n8 * 6 + $n7 * 7 + $n6 * 8 + $n5 * 9 + $n4 * 2 + $n3 * 3 + $n2 * 4 + $n1 * 5;
            $d1 = 11 - (self::mod($d1, 11));
            if ($d1 >= 10) {
                $d1 = 0;
            }
            $d2 = $d1 * 2 + $n12 * 3 + $n11 * 4 + $n10 * 5 + $n9 * 6 + $n8 * 7 + $n7 * 8 + $n6 * 9 + $n5 * 2 + $n4 * 3 + $n3 * 4 + $n2 * 5 + $n1 * 6;
            $d2 = 11 - (self::mod($d2, 11));
            if ($d2 >= 10) {
                $d2 = 0;
            }
            $retorno = '';
            if ($compontos == 1) {
                $retorno = '' . $n1 . $n2 . "." . $n3 . $n4 . $n5 . "." . $n6 . $n7 . $n8 . "/" . $n9 . $n10 . $n11 . $n12 . "-" . $d1 . $d2;
            } else {
                $retorno = '' . $n1 . $n2 . $n3 . $n4 . $n5 . $n6 . $n7 . $n8 . $n9 . $n10 . $n11 . $n12 . $d1 . $d2;
            }

            return $retorno;
        }

        static function objectToArray($obj)
        {
            if (is_object($obj))
                $obj = (array)$obj;
            if (is_array($obj)) {
                $new = array();
                foreach ($obj as $key => $val) {
                    $new[$key] = self::objectToArray($val);
                }
            } else {
                $new = $obj;
            }

            return $new;
        }

        static public function sendMailLog($error, $name)
        {
            $templateVars = array(
                '{name}' => $name,
                '{message}' => $error->getMessage(),
                '{file}' => $error->getFile(),
                '{line}' => $error->getLine(),
                '{date}' => date('d-M-Y H:i:s'),
            );

            Mail::Send('log-error', 'Sistema de Log - Quote Traveling', $templateVars, 'sysanakin@gmail.com', 'Sistema de Log - Quote Traveling');
        }

        static public function sendMailTracking($stage, $token, $description = NULL, $code = NULL)
        {
            try {
                $templateVars = array(
                    '{token}' => $token,
                    '{stage}' => $stage,
                    '{description}' => $description,
                    '{code}' => $code,
                    '{date}' => date('d-M-Y H:i:s'),
                );

                Mail::Send('tracking', 'Tracking - Quote Traveling', $templateVars, 'sysanakin@gmail.com', 'Tracking - Quote Traveling');
            } catch (Exception $e) {

            }
        }

        static function in_array_column($text, $column, $array)
        {
            if (!empty($array) && is_array($array)) {
                foreach ($array as $i => $row) {
                    if ($array[$i][$column] == $text || strcmp($array[$i][$column], $text) == 0)
                        return $i;
                }
            }

            return FALSE;
        }

        static function createSessionBase64($elements = array())
        {

            $session = '';
            foreach ($elements as $argument => $value) {
                $session .= $value . '/';
            }

            $session = base64_encode(substr($session, 0, -1));

            return $session;

        }

        # TODO
        # INCLUIR TCPDF -------------------------------------------------------------------------------------------
        static function genPDF($dataPost, $filename, $page_orientation = 'P')
        {
            if (empty($dataPost)) {
                return FALSE;
            }

            // create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A1', TRUE, 'UTF-8', FALSE);


            // set default header data
            $pdf->SetHeaderData('', '', PDF_HEADER_TITLE . '', PDF_HEADER_STRING);

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // set font
            $pdf->SetFont('dejavusans', '', 10);

            // add a page
            $pdf->AddPage();

            // output the HTML content
            $pdf->writeHTML($dataPost, TRUE, FALSE, TRUE, FALSE, '');

            // reset pointer to the last page
            $pdf->lastPage();

            //Close and output PDF document
            $path = str_replace('classes', '', dirname(__FILE__)) . "files\\";
            $pdf->Output($path . $filename, 'F');

            return Tools::getHttpHost(TRUE, FALSE) . _BASE_URI_ . "files/" . $filename;
        }

        static function isRequestAjax()
        {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        static function setMessage($page, $message, $type = NULL)
        {
            header('location: ' . $page . '?message=' . $message . '&type=' . $type);
        }

        public function uploadFile($uploadData, $folder, $size = NULL)
        {

            if ($uploadData['size'] == 0 || $uploadData['error'] !== 0) {
                return array('success' => FALSE);
            }

            preg_match("/\.(png|jpg|jpeg){1}$/i", $uploadData["name"], $type_file);

            if (empty($type_file[1])) {
                return array('success' => FALSE);
            } else {
                $fileName = md5(uniqid(time())) . "." . $type_file[1];

                $uploadPath = $folder . '/' . $fileName;

                if (!file_exists($folder)) {
                    mkdir($folder);
                }

                if (move_uploaded_file($uploadData['tmp_name'], $uploadPath)) {
                    return array('success' => TRUE, 'file' => $fileName, 'type_file' => $uploadData["type"]);
                } else {
                    return array('success' => FALSE);
                }
            }
        }
    }

?>
