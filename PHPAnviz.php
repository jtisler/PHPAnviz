<?php

class PHPAnviz {

    const ACK_SUCCESS = 0x00; //operation successful
    const ACK_FAIL = 0x01; //operation failed
    const ACK_FULL = 0x04; //user full
    const ACK_EMPTY = 0x05; //user empty
    const ACK_NO_USER = 0x06; //user does not exist
    const ACK_TIME_OUT = 0x08; //capture timeout
    const ACK_USER_OCCUPIED = 0x0A; //user already exists
    const ACK_FINGER_OCCUPIED = 0x0B; //fingerprint already exists
    
    const CLEAR_ALL = 0x00; //clear all records
    const CLEAR_NEW = 0x01; //clear all "new records" flag
    const CLEAR_NEW_PARTIALY = 0x02; //clear the designated amount of "new records sign"

    //device id
    private $id;
    
    //device port
    private $port;
    
    //gearman client
    private $client;
    
    //config array
    private $config;

    function __construct($id, $port, $configFilePath = '') {
        $port = substr($port, 0, 1) != ":" ? ":" . $port : $port;

        $this->id = dechex($id);
        $this->port = $port;

        $this->config = $this->loadConfig($configFilePath);

        $this->client = new GearmanClient();
        $this->client->addServer($this->config['gearman-server']);
    }

    /**
     * @param string $configFilePath - custom path to config file
     * @return array
     */
    
    function loadConfig($configFilePath = '') {
        $configFile = $configFilePath == '' ? 'config.ini' : $configFilePath;

        return parse_ini_file($configFile);
    }

    /**
     * Converts hex to string
     * @param string $hex
     * @return string
     */
    private function hex2str($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i+=2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }

        return $str;
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

    private function request($command, $data = '', $len = -1) {
        $req = [
            'id' => $this->id,
            'port' => $this->port,
            'command' => (string) $command,
            'data' => $data,
            'length' => $len == -1 ? strlen($data) / 2 : $len
        ];

        $res = $this->client->doNormal("Anviz", json_encode($req));

        return $this->parseResponse($res);
    }

    function getInfo1() {
        $res = $this->request(30);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = [
                'firmware_version' => $this->hex2str(implode(array_slice($res['data'], 0, 8))),
                'pass_length' => hexdec($res['data'][8][0]),
                'pass' => $res['data'][8][1] . implode(array_slice($res['data'], 9, 2)),
                'sleep_time' => hexdec($res['data'][11]),
                'volume' => hexdec($res['data'][12]),
                'language' => hexdec($res['data'][13]),
                'datetime_format' => $res['data'][14],
                'attendance_state' => hexdec($res['data'][15]),
                'language_setting_flag' => hexdec($res['data'][16]),
                'command_version' => hexdec($res['data'][17]),
            ];

            return $data;
        }

        return false;
    }

    function setInfo1($pass = 0xFFFFFF, $sleep_time = 0xFF, $volume = 0xFF, $language = 0xFF, $dt_format = 0xFF, $attendance_state = 0xFF, $language_setting_flag = 0xFF, $reserved = 0x00) {

        if (!$sleep_time || $sleep_time == '' || !is_numeric($sleep_time) || $sleep_time > 250 || $sleep_time < 0)
            $sleep_time = 0xFF;

        if (!$volume || $volume == '' || !is_numeric($volume) || $volume > 5 || $volume < 0)
            $volume = 0xFF;

        if (!$language || $language == '' || !is_numeric($language) || $language > 16 || $language < 0)
            $language = 0xFF;

        if (!$attendance_state || $attendance_state == '' || !is_numeric($attendance_state) || $attendance_state > 15 || $attendance_state < 0)
            $attendance_state = 0xFF;


        $data = sprintf("%06x%02x%02x%02x%02x%02x%02x%02x", $pass, $sleep_time, $volume, $language, $dt_format, $attendance_state, $language_setting_flag, $reserved);

        $res = $this->request(31, $data);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            return true;
        }

        return false;
    }

    function getInfo2() {
        $res = $this->request(32);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = [
                'fingerprint_comparison_precision' => hexdec($res['data'][0]),
                'fixed_wiegand_head_code' => hexdec($res['data'][1]),
                'wiegand_option' => hexdec($res['data'][2]),
                'work_code_permission' => hexdec($res['data'][3]),
                'real-time_mode_setting' => hexdec($res['data'][4]),
                'fp_auto_update' => hexdec($res['data'][5]),
                'relay_mode' => hexdec($res['data'][6]),
                'lock_delay' => hexdec($res['data'][7]),
                'memory_full_alarm' => hexdec(implode(array_slice($res['data'], 8, 2))),
                'repeat_attendance_delay' => hexdec($res['data'][11]),
                'door_sensor_delay' => hexdec($res['data'][12]),
                'scheduled_bell_delay' => hexdec($res['data'][13]),
                'reserved' => hexdec($res['data'][14])
            ];

            return $data;
        }

        return false;
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
        }

        return false;
    }

    function setDateTime($dateTime = '') {
        if ($dateTime == '') {
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

        foreach ($ts as $key => $value) {
            $ts[$key] = sprintf('%02s', dechex($value));
        }

        $data = implode($ts);

        $res = $this->request(39, $data);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            return true;
        }

        return false;
    }

    function getTCPIPParameters() {
        $res = $this->request('3A');

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {

            $data = [
                'ip_address' => long2ip(hexdec(implode(array_slice($res['data'], 0, 4)))),
                'subnet_mask' => long2ip(hexdec(implode(array_slice($res['data'], 4, 4)))),
                'mac_address' => implode(array_slice($res['data'], 8, 6)),
                'default_gateway' => long2ip(hexdec(implode(array_slice($res['data'], 14, 4)))),
                'server_ip' => long2ip(hexdec(implode(array_slice($res['data'], 18, 4)))),
                'far_limit' => hexdec($res['data'][22]),
                'comm_port' => implode(array_slice($res['data'], 23, 2)),
                'tcpip_mode' => hexdec($res['data'][25]),
                'dhcp_limit' => hexdec($res['data'][26])
            ];
            return $data;
        }

        return false;
    }

    function getRecordInformation() {
        $res = $this->request('3C');

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {

            $data = [
                'user_amount' => hexdec(implode(array_slice($res['data'], 0, 3))),
                'fp_amount' => hexdec(implode(array_slice($res['data'], 3, 3))),
                'password_amount' => hexdec(implode(array_slice($res['data'], 6, 3))),
                'card_amount' => hexdec(implode(array_slice($res['data'], 9, 3))),
                'all_record_amount' => hexdec(implode(array_slice($res['data'], 11, 3))),
                'new_record_amount' => hexdec(implode(array_slice($res['data'], 15, 3))),
            ];

            return $data;
        }

        return false;
    }

    function downloadTARecords() {
        $res = $this->request('40', '0201');

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = [];

            for ($i = 0; $i < hexdec($res['data'][0]); $i++) {
                $event = [
                    'user_code' => hexdec(implode(array_slice($res['data'], $i * 14 + 1, 5))),
                    'datetime' => date('Y-m-d H:i:s', hexdec(implode(array_slice($res['data'], $i * 14 + 6, 4))) + (strtotime('2000-01-01 00:00:00') - strtotime('1970-01-01 02:00:00'))),
                    'backup_code' => hexdec($res['data'][$i * 14 + 10]),
                    'record_type' => hexdec($res['data'][$i * 14 + 11]),
                    'work_type' => hexdec(implode(array_slice($res['data'], $i * 14 + 12, 2))),
                ];

                $data[] = $event;

                return $data;
            }
        }

        return false;
    }

    function downloadStaffInfo($type = 0x01, $amount = 0x08) {
        $data = sprintf("%02x%02x", $type, $amount);
        $res = $this->request('72', $data);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = [];

            for ($i = 0; $i < hexdec($res['data'][0]); $i++) {
                $employee = [
                    'user_id' => hexdec(implode(array_slice($res['data'], $i * 30 + 1, 5))),
                    'pwd' => implode(array_slice($res['data'], $i * 30 + 6, 3)),
                    'card_id' => implode(array_slice($res['data'], $i * 30 + 9, 4)),
                    'name' => implode(array_slice($res['data'], $i * 30 + 13, 20)),
                    'department' => $res['data'][$i * 30 + 33],
                    'group' => $res['data'][$i * 30 + 34],
                    'attendance_mode' => $res['data'][$i * 30 + 35],
                    'fp_enroll_state' => implode(array_slice($res['data'], $i * 30 + 36, 2)),
                    'pwd_8_digit' => $res['data'][$i * 30 + 38],
                    'keep' => $res['data'][$i * 30 + 39],
                    'special_info' => $res['data'][$i * 30 + 40],
                ];

                $data[] = $employee;
            }

            return $data;
        }

        return false;
    }

    function getDeviceId() {
        $res = $this->request(74);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            
            $data = [
                'id' => hexdec(implode($res['data']))
            ];
            
            return $data;
        }

        return false;
    }

    function setDeviceId($id) {
        $data = sprintf('%08x', $id);

        $res = $this->request(75, $data);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            return true;
        }

        return false;
    }

    function clearRecords($type = 0x01, $amount = 0xFFFF) {

        $data = sprintf("%02x%04x", $type, $amount);

        $res = $this->request('4E', $data);
        
        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            return true;
        }

        return false;
    }

}
