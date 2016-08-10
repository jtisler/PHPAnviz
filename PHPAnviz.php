<?php

class PHPAnviz {

    const ACK_SUCCESS = 0x00;
    const ACK_FAIL = 0x01;

    private $id;
    private $port;
    private $client;
    private $config;

    function __construct($id, $port, $configFilePath = '') {
        $port = substr($port, 0, 1) != ":" ? ":" . $port : $port;

        $this->id = $id;
        $this->port = $port;

        $this->config = $this->loadConfig($configFilePath);

        $this->client = new GearmanClient();
        $this->client->addServer($this->config['gearman-server']);
    }

    function loadConfig($configFilePath = '') {
        $configFile = $configFilePath == '' ? 'config.ini' : $configFilePath;

        return parse_ini_file($configFile);
    }

    private function parseResponse($res) {

        $resArr = str_split($res, 2);

        $output = [];

        $output['stx'] = implode(array_slice($resArr, 0, 1));
        $output['ch'] = hexdec(implode(array_slice($resArr, 1, 4)));
        $output['ack'] = implode(array_slice($resArr, 5, 1));
        $output['ret'] = implode(array_slice($resArr, 6, 1));
        $output['len'] = hexdec(implode(array_slice($resArr, 7, 2)));
        $output['data'] = array_slice($resArr, 9, $output['len']);
        $output['crc'] = implode(array_slice($resArr, -2, 2));

        return $output;
    }
    
    private function request($command, $data = '', $len = -1){
        $req = [
            'id' => $this->id,
            'port' => $this->port,
            'command' => (string)$command,
            'data' => $data,
            'length' => $len == -1 ? strlen($data) / 2 : $len
        ]; 
        
        $res = $this->client->doNormal("Anviz", json_encode($req));
        
        return $this->parseResponse($res);
    }

    function getDateTime($format = 'Y-m-d H:i:s') {
        $res = $this->request(38);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = $res['data'];

            foreach ($data as $key => $value) {
                $data[$key] = hexdec($value);
            }

            $date = sprintf('20%02d-%02d-%02d %02d:%02d:%02d', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);

            return date($format, strtotime($date));
        } else {
            return false;
        }
    }
    
    function setDateTime($dateTime = ''){
        if($dateTime == ''){
            $ts = [
                0 => date('Y') - 2000,
                1 => date('m'),
                2 => date('d'),
                3 => date('H'),
                4 => date('i'),
                5 => date('s')
            ];
        } else {
            $unixTime = strtotime($dateTime);
            
            $ts = [
                0 => date('Y', $unixTime) - 2000,
                1 => date('m', $unixTime),
                2 => date('d', $unixTime),
                3 => date('H', $unixTime),
                4 => date('i', $unixTime),
                5 => date('s', $unixTime)
            ];
        }
        
        foreach($ts as $key => $value){
            $ts[$key] = sprintf('%02s', dechex($value));
        }
        
        $data = implode($ts);
        
        $res = $this->request(39, $data);
        
        if($res['ret'] == PHPAnviz::ACK_SUCCESS){
            return true;
        } else {
            return false;
        }
    }

}
