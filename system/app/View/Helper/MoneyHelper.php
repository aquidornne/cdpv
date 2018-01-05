<?php
    App::uses('AppHelper', 'View/Helper');
    App::uses('CakeNumber', 'Utility');

    class MoneyHelper extends AppHelper
    {
        static public function ceilf($value, $precision = 0)
        {
            $precisionFactor = $precision == 0 ? 1 : pow(10, $precision);
            $tmp = $value * $precisionFactor;
            $tmp2 = (string)$tmp;
            // If the current value has already the desired precision
            if (strpos($tmp2, '.') === FALSE)
                return ($value);
            if ($tmp2[strlen($tmp2) - 1] == 0)
                return $value;

            return ceil($tmp) / $precisionFactor;
        }

        public function ceilFloat($value, $precision = 0)
        {
            $precisionFactor = $precision == 0 ? 1 : pow(10, $precision);
            $tmp = $value * $precisionFactor;
            $tmp2 = (string)$tmp;
            // If the current value has already the desired precision
            if (strpos($tmp2, '.') === FALSE)
                return ($value);
            if ($tmp2[strlen($tmp2) - 1] == 0)
                return $value;

            return ceil($tmp) / $precisionFactor;
        }

        static public function floorf($value, $precision = 0)
        {
            $precisionFactor = $precision == 0 ? 1 : pow(10, $precision);
            $tmp = $value * $precisionFactor;
            $tmp2 = (string)$tmp;
            // If the current value has already the desired precision
            if (strpos($tmp2, '.') === FALSE)
                return ($value);
            if ($tmp2[strlen($tmp2) - 1] == 0)
                return $value;

            return floor($tmp) / $precisionFactor;
        }

        static public function price_round($value, $precision = 0, $currency_id = 0)
        {
            $currency = Configure::read('currency');
            $currency = $currency[(($currency_id == 0) ? 1 : $currency_id)];

            $method = intval($currency['round_mode']);

            if ($method == $currency['round_up'])
                return self::ceilf($value, $precision);
            elseif ($method == $currency['round_down'])
                return self::floorf($value, $precision);

            return round($value, $precision);
        }

        public function displayPrice($price, $sign = TRUE, $c_format = NULL, $currency_id = 1)
        {
            $currency = Configure::read('currency');
            $currency = $currency[(($currency_id == 0) ? 1 : $currency_id)];

            $c_char = (is_array($currency) ? $currency['sign'] : $currency['sign']);
            $c_format = (is_null($c_format) ? $currency['format'] : $c_format);
            $c_decimals = (is_array($currency) ? intval($currency['decimals']) : intval($currency['decimals'])) * $currency['precision'];
            $c_blank = (is_array($currency) ? $currency['blank'] : $currency['blank']);
            $blank = (($c_blank and $sign) ? ' ' : '');
            $ret = 0;
            if (($isNegative = ($price < 0)))
                $price *= -1;
            $price = self::price_round($price, $c_decimals, $currency_id);
            switch ($c_format) {
                /* X 0,000.00 */
                case 1:
                    $ret = ($sign ? $c_char : '') . $blank . number_format($price, $c_decimals, '.', ',');
                    break;
                /* 0 000,00 X*/
                case 2:
                    $ret = number_format($price, $c_decimals, ',', ' ') . $blank . ($sign ? $c_char : '');
                    break;
                /* X 0.000,00 */
                case 3:
                    $ret = ($sign ? $c_char : '') . $blank . number_format($price, $c_decimals, ',', '.');
                    break;
                /* 0,000.00 X */
                case 4:
                    $ret = number_format($price, $c_decimals, '.', ',') . $blank . ($sign ? $c_char : '');
                    break;
                /* 0000.00 X */
                case 5:
                    $ret = number_format($price, $c_decimals, '.', '') . $blank . ($sign ? $c_char : '');
                    break;
            }
            if ($isNegative)
                $ret = '-' . $ret;

            return $ret;
        }

        public function convertPrice($price, $currency_id = 1)
        {
            $currency = Configure::read('currency');
            $currency = $currency[(($currency_id == 0) ? 1 : $currency_id)];

            $price = str_replace('.', '', $price);
            $price = str_replace(',', '.', $price);

            $c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency['conversion_rate']);

            return $price *= $c_rate;
        }

        public function calculateValues($value1, $value2, $operation = NULL, $convertValue1 = FALSE, $convertValue2 = FALSE, $displayPrice = TRUE, $html = FALSE)
        {
            if ($convertValue1) {
                $value1 = $this->convertPrice($value1);
            }
            if ($convertValue2) {
                $value2 = $this->convertPrice($value2);
            }

            switch ($operation) {
                case "+":
                    $result = $value1 + $value2;
                    break;
                case "-":
                    $result = $value1 - $value2;
                    break;
                case "rate":
                    $result = ((($value1 / 100.0) * ($value2 / 100.0)) * 100);
                    break;
                case "*":
                    $result = $value1 * $value2;
                    break;
                case "/":
                    $result = $value1 / $value2;
                    break;
                case "%":
                    $result = (($value2 / 100) * $value1);
                    break;
                case "commision":
                    $result = (($value2 / 100.0) * $this->convertPrice($value1));
                    break;
                case "percentage":
                    return (($value1 / $value2) * 100);
                    break;
                case "grievance":
                    return (($value2 / $value1) * 100);
                    break;
            }

            return (($displayPrice) ? (($html) ? $this->displayPrice($result) : $this->displayPrice($result, FALSE)) : $result);
        }

        public function statusOfPay($pay, $date, $html = TRUE, $circle = FALSE)
        {

            $statusOfPay = Configure::read('status-of-pay');

            if ($pay) { #Pago
                $tooltip = 'data-placement="top" title="" data-trigger="hover" data-toggle="tooltip" data-original-title="' . $statusOfPay[1]['name'] . '"';

                return (($html) ? '<span ' . (($circle) ? $tooltip : '') . ' class=" ' . ((!$circle) ? 'label' : 'circle') . ' ' . ((!$circle) ? $statusOfPay[1]['label'] : $statusOfPay[1]['circle']) . '">' . ((!$circle) ? __($statusOfPay[1]['name']) : '') . '</span>' : ((!$circle) ? __($statusOfPay[1]['name']) : ''));
            } else {
                if (strtotime(date('Y-m-d')) == strtotime($date)) { #Vence Hoje
                    $tooltip = 'data-placement="top" title="" data-trigger="hover" data-toggle="tooltip" data-original-title="' . $statusOfPay[4]['name'] . '"';

                    return (($html) ? '<span ' . (($circle) ? $tooltip : '') . ' class="' . ((!$circle) ? 'label' : 'circle') . ' ' . ((!$circle) ? $statusOfPay[4]['label'] : $statusOfPay[4]['circle']) . '">' . ((!$circle) ? __($statusOfPay[4]['name']) : '') . '</span>' : ((!$circle) ? __($statusOfPay[4]['name']) : ''));
                }

                if (strtotime($date) > strtotime(date('Y-m-d'))) { #À Vencer
                    $tooltip = 'data-placement="top" title="" data-trigger="hover" data-toggle="tooltip" data-original-title="' . $statusOfPay[2]['name'] . '"';

                    return (($html) ? '<span ' . (($circle) ? $tooltip : '') . ' class="' . ((!$circle) ? 'label' : 'circle') . ' ' . ((!$circle) ? $statusOfPay[2]['label'] : $statusOfPay[2]['circle']) . '">' . ((!$circle) ? __($statusOfPay[2]['name']) : '') . '</span>' : ((!$circle) ? __($statusOfPay[2]['name']) : ''));
                }

                if (strtotime($date) < strtotime(date('Y-m-d'))) { #Atrasado
                    $tooltip = 'data-placement="top" title="" data-trigger="hover" data-toggle="tooltip" data-original-title="' . $statusOfPay[3]['name'] . '"';

                    return (($html) ? '<span ' . (($circle) ? $tooltip : '') . ' class="' . ((!$circle) ? 'label' : 'circle') . ' ' . ((!$circle) ? $statusOfPay[3]['label'] : $statusOfPay[3]['circle']) . '">' . ((!$circle) ? __($statusOfPay[3]['name']) : '') . '</span>' : ((!$circle) ? __($statusOfPay[3]['name']) : ''));
                }
            }
        }
    }