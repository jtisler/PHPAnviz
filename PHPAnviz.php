<?php

class PHPAnviz {

    protected $crc_table = [
        0x0000, 0x1189, 0x2312, 0x329B, 0x4624, 0x57AD, 0x6536, 0x74BF, 0x8C48, 0x9DC1,
        0xAF5A, 0xBED3, 0xCA6C, 0xDBE5, 0xE97E, 0xF8F7, 0x1081, 0x0108, 0x3393, 0x221A,
        0x56A5, 0x472C, 0x75B7, 0x643E, 0x9CC9, 0x8D40, 0xBFDB, 0xAE52, 0xDAED, 0xCB64,
        0xF9FF, 0xE876, 0x2102, 0x308B, 0x0210, 0x1399, 0x6726, 0x76AF, 0x4434, 0x55BD,
        0xAD4A, 0xBCC3, 0x8E58, 0x9FD1, 0xEB6E, 0xFAE7, 0xC87C, 0xD9F5, 0x3183, 0x200A,
        0x1291, 0x0318, 0x77A7, 0x662E, 0x54B5, 0x453C, 0xBDCB, 0xAC42, 0x9ED9, 0x8F50,
        0xFBEF, 0xEA66, 0xD8FD, 0xC974, 0x4204, 0x538D, 0x6116, 0x709F, 0x0420, 0x15A9,
        0x2732, 0x36BB, 0xCE4C, 0xDFC5, 0xED5E, 0xFCD7, 0x8868, 0x99E1, 0xAB7A, 0xBAF3,
        0x5285, 0x430C, 0x7197, 0x601E, 0x14A1, 0x0528, 0x37B3, 0x263A, 0xDECD, 0xCF44,
        0xFDDF, 0xEC56, 0x98E9, 0x8960, 0xBBFB, 0xAA72, 0x6306, 0x728F, 0x4014, 0x519D,
        0x2522, 0x34AB, 0x0630, 0x17B9, 0xEF4E, 0xFEC7, 0xCC5C, 0xDDD5, 0xA96A, 0xB8E3,
        0x8A78, 0x9BF1, 0x7387, 0x620E, 0x5095, 0x411C, 0x35A3, 0x242A, 0x16B1, 0x0738,
        0xFFCF, 0xEE46, 0xDCDD, 0xCD54, 0xB9EB, 0xA862, 0x9AF9, 0x8B70, 0x8408, 0x9581,
        0xA71A, 0xB693, 0xC22C, 0xD3A5, 0xE13E, 0xF0B7, 0x0840, 0x19C9, 0x2B52, 0x3ADB,
        0x4E64, 0x5FED, 0x6D76, 0x7CFF, 0x9489, 0x8500, 0xB79B, 0xA612, 0xD2AD, 0xC324,
        0xF1BF, 0xE036, 0x18C1, 0x0948, 0x3BD3, 0x2A5A, 0x5EE5, 0x4F6C, 0x7DF7, 0x6C7E,
        0xA50A, 0xB483, 0x8618, 0x9791, 0xE32E, 0xF2A7, 0xC03C, 0xD1B5, 0x2942, 0x38CB,
        0x0A50, 0x1BD9, 0x6F66, 0x7EEF, 0x4C74, 0x5DFD, 0xB58B, 0xA402, 0x9699, 0x8710,
        0xF3AF, 0xE226, 0xD0BD, 0xC134, 0x39C3, 0x284A, 0x1AD1, 0x0B58, 0x7FE7, 0x6E6E,
        0x5CF5, 0x4D7C, 0xC60C, 0xD785, 0xE51E, 0xF497, 0x8028, 0x91A1, 0xA33A, 0xB2B3,
        0x4A44, 0x5BCD, 0x6956, 0x78DF, 0x0C60, 0x1DE9, 0x2F72, 0x3EFB, 0xD68D, 0xC704,
        0xF59F, 0xE416, 0x90A9, 0x8120, 0xB3BB, 0xA232, 0x5AC5, 0x4B4C, 0x79D7, 0x685E,
        0x1CE1, 0x0D68, 0x3FF3, 0x2E7A, 0xE70E, 0xF687, 0xC41C, 0xD595, 0xA12A, 0xB0A3,
        0x8238, 0x93B1, 0x6B46, 0x7ACF, 0x4854, 0x59DD, 0x2D62, 0x3CEB, 0x0E70, 0x1FF9,
        0xF78F, 0xE606, 0xD49D, 0xC514, 0xB1AB, 0xA022, 0x92B9, 0x8330, 0x7BC7, 0x6A4E,
        0x58D5, 0x495C, 0x3DE3, 0x2C6A, 0x1EF1, 0x0F78
    ];

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

    function crc16($b) {
        $crc = 0xFFFF;

        for ($l = 0; $l < strlen($b); $l++) {
            $crc ^= ord($b[$l]);
            $crc = ($crc >> 8) ^ $this->crc_table[$crc & 255];
        }

        $res = strtoupper(dechex($crc));

        //if crc has length less than 4 add leading zero
        $res = sprintf("%04s", $res);

        return($res[2] . $res[3] . $res[0] . $res[1]);
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

    private function parseResponse($response) {

        $output = [];

        foreach ($response as $res) {
            $resArr = str_split($res, 2);

            $json['stx'] = implode(array_slice($resArr, 0, 1));
            $json['ch'] = hexdec(implode(array_slice($resArr, 1, 4)));
            $json['ack'] = implode(array_slice($resArr, 5, 1));
            $json['ret'] = implode(array_slice($resArr, 6, 1));
            $json['len'] = hexdec(implode(array_slice($resArr, 7, 2)));
            $json['data'] = array_slice($resArr, 9, $json['len']);
            $json['crc'] = implode(array_slice($resArr, -2, 2));

            $output[] = $json;
        }

        return $output;
    }

    function buildRequest($command, $data = '', $len = -1) {

        $len = $len == -1 ? strlen($data) / 2 : $len;

        $req = sprintf("A5%08x%02x%04x%s", $this->id, $command, $len, $data);

        $req .= $this->crc16(hex2bin($req));

        return $req;
    }

    private function request($commands) {

        $req = [
            'id' => $this->id,
            'port' => $this->port,
            'command' => $commands
        ];

        $res = $this->client->doNormal("Anviz", json_encode($req));

        return $this->parseResponse(json_decode($res));
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
        $commands = [
            0 => $this->buildRequest(0x38)
        ];

        $resArr = $this->request($commands);

        $output = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
                $data = $res['data'];

                foreach ($data as $key => $value) {
                    $data[$key] = hexdec($value);
                }

                $date = sprintf('20%02d-%02d-%02d %02d:%02d:%02d', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);

                $output[] = date($format, strtotime($date));
            } else {
                $output[] = false;
            }
        }

        return $output;
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

        $commands = [$this->buildRequest(0x3A)];

        $resArr = $this->request($commands);

        $output = [];

        foreach ($resArr as $res) {
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
                $output[] = $data;
            } else {
                $output[] = false;
            }
        }

        return $output;
    }

    function getRecordInformation() {
        $commands = [$this->buildRequest(0x3C)];

        $resArr = $this->request($commands);

        $output = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
                $data = [
                    'user_amount' => hexdec(implode(array_slice($res['data'], 0, 3))),
                    'fp_amount' => hexdec(implode(array_slice($res['data'], 3, 3))),
                    'password_amount' => hexdec(implode(array_slice($res['data'], 6, 3))),
                    'card_amount' => hexdec(implode(array_slice($res['data'], 9, 3))),
                    'all_record_amount' => hexdec(implode(array_slice($res['data'], 11, 3))),
                    'new_record_amount' => hexdec(implode(array_slice($res['data'], 15, 3))),
                ];

                $output[] = $data;
            } else {
                $output[] = false;
            }
        }

        return $output;
    }

    function downloadTARecords() {
        $recordInfo = $this->getRecordInformation();

        $commands[0] = $this->buildRequest(0x40, '0219');

        for ($i = 25; $i < $recordInfo[0]['new_record_amount']; $i += 25) {
            $commands[] = $this->buildRequest(0x40, '0019');
        }

        $resArr = $this->request($commands);
         
        $data = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {

                for ($i = 0; $i < hexdec($res['data'][0]); $i++) {
                    $event = [
                        'user_code' => hexdec(implode(array_slice($res['data'], $i * 14 + 1, 5))),
                        'datetime' => date('Y-m-d H:i:s', hexdec(implode(array_slice($res['data'], $i * 14 + 6, 4))) + (strtotime('2000-01-01 00:00:00') - strtotime('1970-01-01 02:00:00'))),
                        'backup_code' => hexdec($res['data'][$i * 14 + 10]),
                        'record_type' => hexdec($res['data'][$i * 14 + 11]),
                        'work_type' => hexdec(implode(array_slice($res['data'], $i * 14 + 12, 2))),
                    ];

                    $data[] = $event;

                }
            }
        }
        
        return $data;

    }

    function downloadStaffInfo($type = 0x00, $amount = 0xFF) {

        $recordInfo = $this->getRecordInformation();

        $data = sprintf("%02s%02s", '01', '08');
        $commands[0] = $this->buildRequest('72', $data);
//        $data = sprintf("%02s%02s", '00', '08');
//        $commands[] = $this->buildRequest('72', $data);

        $resArr = $this->request($commands);

        $final = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
                for ($i = 0; $i < hexdec($res['data'][0]); $i++) {
                    $employee = [
                        'user_id' => hexdec(implode(array_slice($res['data'], $i * 40 + 1, 5))),
                        'pwd' => hexdec($res['data'][$i * 40 + 6][1] . implode(array_slice($res['data'], $i * 40 + 7, 2))),
                        'card_id' => hexdec(implode(array_slice($res['data'], $i * 40 + 9, 4))),
                        'name' => $this->hex2str(implode(array_slice($res['data'], $i * 40 + 13, 20))),
                        'department' => $res['data'][$i * 40 + 33],
                        'group' => $res['data'][$i * 40 + 34],
                        'attendance_mode' => $res['data'][$i * 40 + 35],
                        'fp_enroll_state' => implode(array_slice($res['data'], $i * 40 + 36, 2)),
                        'pwd_8_digit' => $res['data'][$i * 40 + 38],
                        'keep' => $res['data'][$i * 40 + 39],
                        'special_info' => $res['data'][$i * 40 + 40],
                    ];

                    $final[] = $employee;
                }
            }
        }

        return $final;
    }

    function fixName($name) {
        $i = 0;

        $newName = '';

        while (strlen($newName) < 40) {
            $newName .= '00' . $name[$i] . $name[$i + 1];
            $i += 2;
        }

        return $newName;
    }

    function uploadStaffInfo($users) {

        $employees = [];

        foreach ($users as $user) {

            $name = unpack('H*', $user[3]);

            $name = $this->fixName($name[1]);

            $employee = [
                'user_id' => sprintf('%010x', $user[0]),
                'pwd' => sprintf('%06x', 0xFFFFFF),
                'card_id' => sprintf('%08x', 0xFFFFFFFF), //$user[2]),
                'name' => sprintf('%040s', $name),
                'department' => sprintf('%02x', 0xFF),
                'group' => sprintf('%02x', 0xFF),
                'attendance_mode' => 'FF',
                'fp_enroll_state' => '0000',
                'pwd_8_digit' => 'FF',
                'keep' => 'FF',
                'special_info' => 'FF',
            ];

            $employees[] = implode($employee);
        }

        $data = sprintf("%02x", count($employees)) . implode($employees);

        $commands = [$this->buildRequest(73, $data)];

        $res = $this->request($commands);

        return $res;
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

    function clearUsers() {

        $commands[] = $this->buildRequest('4D');

        print_r($commands);

        $resArr = $this->request($commands);

        $output = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
                $output[] = true;
            } else {
                $output[] = false;
            }
        }

        return $output;
    }

    function clearRecords($type = 0x01, $amount = 0xFFFF) {

        $data = sprintf("%02x%04x", $type, $amount);

        $commands = $this->buildRequest('4E', $data);

        $resArr = $this->request($commands);
        $output = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
                $output[] = true;
            } else {
                $output[] = false;
            }
        }

        return $output;
    }

}
