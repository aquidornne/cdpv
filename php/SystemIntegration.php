<?php

    if (!function_exists('curl_init')) {
        throw new Exception('System Integration needs the CURL PHP extension.');
    }
    if (!function_exists('json_decode')) {
        throw new Exception('System Integration needs the JSON PHP extension.');
    }

    class SystemIntegration
    {
        protected $clientSecretKey;

        protected $_domainRequest;
        protected $_trustForwarded = FALSE;
        protected $_domain = 'fusioncomunicacao.com/clientes/cdpv/system';
        protected $_controller = 'Webservices';
        protected $_webservice;
        protected $_timeout = 30;
        protected $_sandbox = FALSE;

        private $_validation_map = array(
            'cli002' => array('msg' => 'Você deve informar a clientSecretKey!'),
            'cli003' => array('msg' => 'Não há dados a serem tratados!'),
        );

        /**
         * HTTP response codes array
         *
         * @var array
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         **/
        private $_codes = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out'
        );

        function __construct ($config)
        {

            if (!$config['clientSecretKey']) {
                $this->validationError('cli002');
            }

            $this->clientSecretKey = ((isset($config['clientSecretKey']) && $config['clientSecretKey']) ? $config['clientSecretKey'] : '');

            $this->_domainRequest = $this->getHttpHost();
            $this->_trustForwarded = ((isset($config['trust'])) ? $config['trust'] : (($this->getHttpProtocol() == 'https') ? TRUE : FALSE));

            if (isset($config['sandbox']) and $config['sandbox']) {
                $this->_sandbox = $config['sandbox'];
                $this->_webservice = (($this->_trustForwarded) ? 'https://' : 'http://') . 'localhost/SERVICOS/cdpv/system' . '/' . $this->_controller . '/';
            } else {
                $this->_webservice = (($this->_trustForwarded) ? 'https://' : 'http://') . $this->_domain . '/' . $this->_controller . '/';
            }
        }

        public function setAccessToken ($clientIdInt) { return $this->clientIdInt = $clientIdInt; }

        function transmitData ($method, $data)
        {
            $process = curl_init($this->_webservice . $method . '/');
            curl_setopt($process, CURLOPT_HEADER, FALSE);
            curl_setopt($process, CURLOPT_ENCODING, 'gzip');
            curl_setopt($process, CURLOPT_TIMEOUT, $this->_timeout);
            curl_setopt($process, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($process, CURLOPT_POST, 1);
            $return = curl_exec($process);
            curl_close($process);

            $json = json_decode($return, FALSE);

            if (empty($json)) {
                if ($this->_sandbox)
                    $this->debug($return);
                $this->callException($return);
            }

            return $json;
        }

        private function validationError ($validatioId)
        {
            if (!$validatioId)
                return FALSE;

            $msgError = $this->_validation_map[$validatioId];

            return new ArrayObject(array('success' => FALSE, 'msg' => $msgError['msg']), ArrayObject::STD_PROP_LIST);
        }

        public function getNameError ($code)
        {
            if (!$code)
                return FALSE;

            return ((isset($this->_codes[$code])) ? $this->_codes[$code] : '');
        }

        private function callException ($error)
        {
            throw new Exception(__METHOD__ . ' -> ' . $error);
        }

        static public function debug ($object, $kill = TRUE)
        {
            echo '<pre style="text-align: left;">' . 'BEGIN DEBUG -->> ';
            print_r($object);
            echo '</pre><br />';
            if ($kill)
                die('DIE');

            return ($object);
        }

        protected function getHttpProtocol ()
        {
            if ($this->_trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
                if ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                    return 'https';
                }

                return 'http';
            }
            /*apache + variants specific way of checking for https*/
            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1)
            ) {
                return 'https';
            }
            /*nginx way of checking for https*/
            if (isset($_SERVER['SERVER_PORT']) &&
                ($_SERVER['SERVER_PORT'] === '443')
            ) {
                return 'https';
            }

            return 'http';
        }

        protected function getHttpHost ()
        {
            if ($this->_trustForwarded && isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
                return $_SERVER['HTTP_X_FORWARDED_HOST'];
            }

            return $_SERVER['HTTP_HOST'];
        }

        private function toArray ($obj)
        {
            if (is_object($obj)) $obj = (array)$obj;
            if (is_array($obj)) {
                $new = array();
                foreach ($obj as $key => $val) {
                    $new[$key] = self::toArray($val);
                }
            } else {
                $new = $obj;
            }

            return $new;
        }

        public function genProtocol ($prefix)
        {
            return str_pad(date("Ymd") . '.' . mt_rand(), 5, "0", STR_PAD_LEFT) . '/' . $prefix;
        }

        #- -------------------------------------------------------------------------------------------------------------
        //- Methods Integrations

        public function serviceFindClients ($data)
        {
            return $this->transmitData(__FUNCTION__, array('data' => $data, 'clientSecretKey' => $this->clientSecretKey));
        }

        public function serviceFindEvents ($data)
        {
            return $this->transmitData(__FUNCTION__, array('data' => $data, 'clientSecretKey' => $this->clientSecretKey));
        }

        public function serviceFindEventById ($data)
        {
            return $this->transmitData(__FUNCTION__, array('data' => $data, 'clientSecretKey' => $this->clientSecretKey));
        }

        public function serviceAddComment ($data)
        {
            return $this->transmitData(__FUNCTION__, array('data' => $data, 'clientSecretKey' => $this->clientSecretKey));
        }

        public function serviceFindComments ($data)
        {
            return $this->transmitData(__FUNCTION__, array('data' => $data, 'clientSecretKey' => $this->clientSecretKey));
        }

        #- -------------------------------------------------------------------------------------------------------------
    }

?>