<?php
require_once 'BaseRecords.php';

class DataAnalyze extends BaseRecords{
    public function analyze(): array{
        $totalStops = $this->getRecordSize();
        $records2021 = array_filter($this->records, function($data){
            if(empty($data[1])) return false;
            $year = (int)date('Y', strtotime($data[1]));
            return $year == 2021;
        });
        $citationRecords = array_filter($this->records, function($data){
            $action = strtolower((string)($data[12] ?? ''));
            return strpos($action, 'citation') !== false;
        });
        $warningRecords = array_filter($this->records, function($data){
            $action = strtolower((string)($data[12] ?? ''));
            return strpos($action, 'warning') !== false;
        });
        $stops2021 = $this->getRecordSize($records2021);
        $citationCount = $this->getRecordSize($citationRecords);
        $warningCount = $this->getRecordSize($warningRecords);
        $stopsVirginiaBeach2021 = 0;
        $trafficVio = 0;
        $maleWarningVio = 0;
        $maleCitationVio = 0;
        $jan2021Stops = 0;
        $dec2020Stops = 0;
        $blackAfricanAmericanStops = 0;

        foreach($this->records as $row){
            $dateStr = $row[1] ?? null;
            $year = null;
            $month = null;
            if($dateStr){
                $time = strtotime($dateStr);
                $year = (int)date('Y', $time);
                $month = (int)date('n', $time); // 1-12
            }
            $jurisdiction = strtolower((string)($row[4] ?? ''));
            $reason = strtolower((string)($row[5] ?? ''));
            $race = strtolower((string)($row[7] ?? ''));
            $gender = strtolower((string)($row[10] ?? ''));
            $action = strtolower((string)($row[12] ?? ''));

            if($year === 2021){
                if (strpos($jurisdiction, 'virginia beach') !== false){
                    $stopsVirginiaBeach2021++;
                }
                if ($month === 1){
                    $jan2021Stops++;
                }
            }elseif($year === 2020 && $month === 12){
                $dec2020Stops++;
            }

            if(strpos($reason, 'traffic') !== false){
                $trafficVio++;
            }

            if(strpos($action, 'warning') !== false && $gender === 'male'){
                $maleWarningVio++;
            }
            if(strpos($action, 'citation') !== false && $gender === 'male'){
                $maleCitationVio++;
            }
            if(strpos($race, 'black') !== false || strpos($race, 'african american') !== false){
                $blackAfricanAmericanStops++;
            }
        }
        $ages2021 = array_map(function($data){
            return isset($data[9]) && is_numeric($data[9]) ? (int)$data[9] : null;
        }, $records2021);
        $validAges = array_filter($ages2021, function($age){
            return $age !== null;
        });
        $averageAge = 0;
        if($this->getRecordSize($validAges) > 0){
            $averageAge += array_sum($validAges);
            $averageAge /= $this->getRecordSize($validAges);
        }
        $pctTrafficVio = $this->computePercentage($trafficVio, $totalStops);
        $maleWarningPct = $this->computePercentage($maleWarningVio, $warningCount);
        $maleCitationPct = $this->computePercentage($maleCitationVio, $citationCount);
        $pctCitationVio = $this->computePercentage($citationCount, $totalStops);
        $blackAfricanAmericanPct = $this->computePercentage($blackAfricanAmericanStops, $totalStops);
        $resultList = [];
        array_push($resultList, 
            ["question" => "Stops conducted in 2021", "value" => (string)$stops2021],
            ["question" => "Stops in Virginia Beach in 2021", "value" => (string)$stopsVirginiaBeach2021],
            ["question" => "Average age of people stopped in 2021", "value" => "{$averageAge} years"],
            ["question" => "Percentage of stops that are traffic violations", "value" => "{$pctTrafficVio}%"],
            ["question" => "Percentage of warnings issued to males", "value" => "{$maleWarningPct}%"],
            ["question" => "Percentage of citations issued to males", "value" => "{$maleCitationPct}%"],
            ["question" => "Percentage of total stops resulting in citations", "value" => "{$pctCitationVio}%"],
            ["question" => "Stops in January 2021", "value" => (string)$jan2021Stops],
            ["question" => "Stops in December 2020", "value" => (string)$dec2020Stops],
            ["question" => "Percentage of stopped people who are Black or African American", "value" => "{$blackAfricanAmericanPct}%"]
        );
        $finalOutput = [];
        for ($i = 0; $i < count($resultList); $i++) {
            $index = $i + 1;
            $question = $resultList[$i]['question'];
            $val = $resultList[$i]['value'];
            $formattedQuestion = "Question {$index}: {$question}";
            $finalOutput[] = [
                'question' => $formattedQuestion,
                'result' => $val
            ];
        }
        return $finalOutput;
    }
}