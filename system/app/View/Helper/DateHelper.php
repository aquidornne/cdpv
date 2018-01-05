<?php
    App::uses('AppHelper', 'View/Helper');

    class DateHelper extends AppHelper
    {
        public function longDate($date = FALSE, $day_week = TRUE, $day_week_full = TRUE, $year = TRUE)
        {
            if ($date) {
                $month = date('m', strtotime($date));
            } else {
                $month = date('m');
                $date = date('Y-m-d');
            }

            $monthes = array(
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

            return ($day_week ? $dias[date('w', strtotime($date))] : '') . (($day_week and $day_week_full) ? ', ' : ' ') . ($day_week_full ? date('d', strtotime($date)) . ' de ' . $monthes[$month] : '') . ($year ? ' de ' . date('Y', strtotime($date)) : '');
        }

        public function humanizeDate($date, $group = FALSE)
        {

            if (!empty($date) and !in_array($date, array('00-00-0000', '0000-00-00', '0000-00-00 00:00:00', '00-00-0000 00:00:00'))) {
                $timestamp = strtotime($date);
                $date = date("Y-m-d", strtotime($date));

                //type cast, current time, difference in timestamps
                $timestamp = (int)$timestamp;
                $current_time = time();
                $diff = $current_time - $timestamp;

                //intervals in seconds
                $intervals = array(
                    'year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute' => 60
                );

                if ($group) {
                    if (strtotime($date) == strtotime(date('Y-m-d'))) {
                        return 'Hoje';
                    }

                    if (strtotime($date) == strtotime(date('Y-m-d', strtotime("-1 days")))) {
                        return 'Ontem';
                    }

                    if ($diff >= $intervals['day'] && $diff < $intervals['week']) {
                        return $this->longDate(date("Y-m-d", strtotime($date)), TRUE, FALSE, FALSE);
                    }

                    if ($diff >= $intervals['day'] && $diff > $intervals['week'] && date('Y', strtotime($date)) == date('Y')) {
                        return $this->longDate(date("Y-m-d", strtotime($date)), FALSE, TRUE, FALSE);
                    }

                    if (date('Y', strtotime("-1 year", strtotime(date('Y-m-d')))) < date('Y')) {
                        return $this->longDate(date("Y-m-d", strtotime($date)), FALSE, TRUE, TRUE);
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
            } else {
                return 'não informado';
            }
        }

        public function formatDate($date, $lang = 'br', $hour = FALSE, $separator = '-')
        {
            try {
                if (!empty($date) and !in_array($date, array('00-00-0000', '0000-00-00', '0000-00-00 00:00:00', '00-00-0000 00:00:00'))) {
                    if ($lang == 'us-year-month') {
                        $date = explode('-', $date);
                        $date = $date[1] . '-' . $date[0];
                    }

                    $date = new DateTime($date);

                    if ($lang == 'us')
                        return $date->format('Y' . $separator . 'm' . $separator . 'd' . ($hour ? ' H:i:s' : ''));
                    elseif ($lang == 'us-year-month')
                        return $date->format('Y' . $separator . 'm');
                    elseif ($lang == 'br')
                        return $date->format('d' . $separator . 'm' . $separator . 'Y' . ($hour ? ' H:i:s' : ''));
                    elseif ($lang == 'br-year-month')
                        return $date->format('m' . $separator . 'Y');
                    else
                        return FALSE;
                } else {
                    return $date;
                }
            } catch (Exception $e) {
                return FALSE;
            }
        }

        function dateDiff($start, $end = FALSE)
        {
            $return = array();

            try {
                $start = new DateTime($start);
                $end = new DateTime($end);
                $form = $start->diff($end);
            } catch (Exception $e) {
                return $e->getMessage();
            }

            $display = array('y' => 'year',
                             'm' => 'month',
                             'd' => 'day',
                             'h' => 'hour',
                             'i' => 'minute',
                             's' => 'second');
            foreach ($display as $key => $value) {
                if ($form->$key > 0) {
                    $return[] = $form->$key . ' ' . ($form->$key > 1 ? $value . 's' : $value);
                }
            }

            return implode($return, ', ');
        }

        public function daysBetweenDate($date_int, $date_end)
        {
            try {

                $days = array();

                for ($date = strtotime($date_int); $date <= strtotime($date_end); $date = strtotime("+1 day", $date)) {
                    $days[] = date("Y-m-d", $date);
                }

                return $days;
            } catch (Exception $e) {
                return FALSE;
            }
        }

        public function monthsBetweenDate($date_int, $date_end)
        {
            try {
                $date_int = strtotime($date_int);
                $date_end = strtotime($date_end);

                $diff = $date_int;

                $months[] = date("Y-m", $diff);

                while ($diff < $date_end) {
                    $diff = strtotime('+1 MONTH', $diff);
                    $months[] = date("Y-m", $diff);
                }

                return $months;
            } catch (Exception $e) {
                return FALSE;
            }
        }

        public function generateMonthsInstallments($date, $number_installments)
        {
            try {

                $installments = array();

                for ($i = 0; $i < $number_installments; ++$i) {
                    $installments[] = array('month' => date("Y-m-d", strtotime("+" . $i . " MONTH", strtotime($date))), 'installment' => $i + 1);
                }

                return $installments;
            } catch (Exception $e) {
                return FALSE;
            }
        }

        public function getDayOfWeek($timestamp)
        {
            $date = getdate($timestamp);
            $diaSemana = $date['weekday'];

            if (preg_match('/(sunday|domingo)/mi', $diaSemana))
                $diaSemana = 'Domingo';
            else if (preg_match('/(monday|segunda)/mi', $diaSemana))
                $diaSemana = 'Segunda';
            else if (preg_match('/(tuesday|terça)/mi', $diaSemana))
                $diaSemana = 'Terça';
            else if (preg_match('/(wednesday|quarta)/mi', $diaSemana))
                $diaSemana = 'Quarta';
            else if (preg_match('/(thursday|quinta)/mi', $diaSemana))
                $diaSemana = 'Quinta';
            else if (preg_match('/(friday|sexta)/mi', $diaSemana))
                $diaSemana = 'Sexta';
            else if (preg_match('/(saturday|sábado)/mi', $diaSemana))
                $diaSemana = 'Sábado';

            return $diaSemana;
        }

        public function businessDay($date, $day = TRUE)
        {
            $new_date = explode('-', $date);
            $data = mktime(0, 0, 0, $new_date[1], $new_date[2], $new_date[0]);
            $dia_semana = date("w", $data);

            if ($day) {
                if (($dia_semana != 0) && ($dia_semana != 6)) {
                    return $date;
                } else {
                    if ($dia_semana == 0) {
                        return date("Y-m-d", strtotime("-2 DAY", strtotime($date)));
                    }

                    if ($dia_semana == 6) {
                        return date("Y-m-d", strtotime("-1 DAY", strtotime($date)));
                    }
                }
            } else {
                if (($dia_semana != 0) && ($dia_semana != 6)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        }

        public function lastBusinessDayMonth($month, $year)
        {
            $dias = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $ultimo = mktime(0, 0, 0, $month, $dias, $year);
            $dia = date("j", $ultimo);
            $dia_semana = date("w", $ultimo);

            if ($dia_semana == 0) {
                $dia--;
                $dia--;
            }
            if ($dia_semana == 6)
                $dia--;
            $ultimo = mktime(0, 0, 0, $month, $dia, $year);

            return date("Y-m-d", $ultimo);
        }

        public function hoursRange($lower = 0, $upper = 86400, $step = 3600, $format = '')
        {
            $times = array();

            if (empty($format)) {
                $format = 'g:i a';
            }

            foreach (range($lower, $upper, $step) as $increment) {
                $increment = gmdate('H:i', $increment);

                list($hour, $minutes) = explode(':', $increment);

                $date = new DateTime($hour . ':' . $minutes);

                $times[(string)$increment] = $date->format($format);
            }

            return $times;
        }

        public function minutesRange()
        {
            $options = array();
            $min = array('00', '30');
            foreach (range(0, 23) as $fullhour) {
                foreach ($min as $int) {
                    $options[(($fullhour < 10) ? '0' . $fullhour : $fullhour) . ":" . $int] = (($fullhour < 10) ? '0' . $fullhour : $fullhour) . ":" . $int;
                }
            }

            return $options;
        }

        static function convertToHoursMins($time, $format = '%d:%s')
        {
            settype($time, 'integer');
            if ($time < 0 || $time >= 1440) {
                return;
            }
            $hours = floor($time / 60);
            $minutes = $time % 60;
            if ($minutes < 10) {
                $minutes = '0' . $minutes;
            }

            return sprintf($format, $hours, $minutes);
        }

        static function averageTime($total, $count, $rounding = 0)
        {
            $total = explode(":", strval($total));
            if (count($total) !== 3) return FALSE;
            $sum = $total[0] * 60 * 60 + $total[1] * 60 + $total[2];
            $average = $sum / (float)$count;
            $hours = floor($average / 3600);
            $minutes = floor(fmod($average, 3600) / 60);
            $seconds = number_format(fmod(fmod($average, 3600), 60), (int)$rounding);

            return (($hours < 10) ? '0' . $hours : $hours) . ":" . (($minutes < 10) ? '0' . $minutes : $minutes) . ":" . (($seconds < 10) ? '0' . $seconds : $seconds);
        }
    }