<?php

/**
 * PHPAnviz - PHP library for communication with ANVIZ devices
 * PHP Version 5 +
 * @package PHPAnviz
 * @link https://github.com/jtisler/phpAnviz PHPAnviz github project
 * @author Jerko Tisler <jerko.tisler@gmail.com>
 * @copyright (c) 2016, Jerko Tisler
 * @license https://opensource.org/licenses/MIT MIT
 * 
 */
class PHPAnviz {

    /**
     * CRC Table
     * @var array
     * @access protected
     */
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

    /**
     * Anviz devices are calculating time since 2000-01-01 (for gathering records this it seems it's 2000-01-02 instead of 2000-01-01)
     */
    const ANVIZ_EPOCH = 946767600;

    /**
     * Operation successfull
     */
    const ACK_SUCCESS = 0x00;

    /**
     * Operation failed
     */
    const ACK_FAIL = 0x01;

    /**
     * User full
     */
    const ACK_FULL = 0x04;

    /**
     * User empty
     */
    const ACK_EMPTY = 0x05;

    /**
     * User does not exist
     */
    const ACK_NO_USER = 0x06;

    /**
     * Capture timeout
     */
    const ACK_TIME_OUT = 0x08;

    /**
     * User already exists
     */
    const ACK_USER_OCCUPIED = 0x0A;

    /**
     * Fingerprint already exists
     */
    const ACK_FINGER_OCCUPIED = 0x0B;

    /**
     * Clear all records
     */
    const CLEAR_ALL = 0x00;

    /**
     * Clear all "new records" flag
     */
    const CLEAR_NEW = 0x01;

    /**
     * Clear the designated amount of "new records" flag
     */
    const CLEAR_NEW_PARTIALY = 0x02;

    /**
     * Restart; retrieve all the records (The first data packet must send this data when retrieving all the records)
     */
    const DOWNLOAD_ALL = 0x01;

    /**
     * Restart; retrieve new records (The first data packet must send this data when retrieving the new records)
     */
    const DOWNLOAD_NEW = 0x02;

    /**
     * Device ID
     * @var hex 
     * @access private
     */
    private $id;

    /**
     * Device port
     * @var string
     * @access private 
     */
    private $port;

    /**
     * Instance of GearmanClient
     * @var GearmanClient
     * @access private 
     */
    private $client;

    /**
     * Config array
     * @var array
     * @access private 
     */
    private $config;

    /**
     * Constructor
     * @param int $id
     * @param int $port
     * @param string $configFilePath
     */
    function __construct($id, $port, $configFilePath = '') {
        //Check if port has : prefix, if not add one
        $port = substr($port, 0, 1) != ":" ? ":" . $port : $port;

        $this->id = dechex($id);
        $this->port = $port;

        //Create config
        $this->config = $this->loadConfig($configFilePath);

        //Create instance of Gearman Client
        $this->client = new GearmanClient();
        //Add server
        $this->client->addServer($this->config['gearman-server']);
    }

    /**
     * Calculate crc16
     * @param binary $b
     * @return string
     * @access private
     */
    private function crc16($b) {
        $crc = 0xFFFF;

        for ($l = 0; $l < strlen($b); $l++) {
            $crc ^= ord($b[$l]);
            $crc = ($crc >> 8) ^ $this->crc_table[$crc & 255];
        }

        $crc = strtoupper(dechex($crc));

        //if crc has length less than 4 add leading zero
        $crc = sprintf("%04s", $crc);

        return($crc[2] . $crc[3] . $crc[0] . $crc[1]);
    }

    /**
     * Parse ini file and return it as array
     * @param string $configFilePath - custom path to config file
     * @return array
     * @access private
     */
    private function loadConfig($configFilePath = '') {
        $configFile = $configFilePath == '' ? 'config.ini' : $configFilePath;

        return parse_ini_file($configFile);
    }

    /**
     * Converts hex to string
     * @param string $hex
     * @return string
     * @access private
     */
    private function hex2str($hex) {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i+=2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }

        return $str;
    }

    /**
     * Convert response from:
     * 
     * STX      CH(device code)     ACK(response)               RET(return)     LEN(data length)    DATA            CRC16
     * 0xA5     4 Bytes             1 Byte(command + 0x80)      1 Byte          2 Bytes             0-400 Bytes     2 Bytes
     * 
     * to array
     * 
     * @param string|array $response
     * @param int $type
     * @return array
     * @access private
     */
    private function parseResponse($response, $type) {
        //if type is 1 it means that multiple commands were send, so we need to parse all responses we got
        if ($type == 1) {
            foreach ($response as $res) {

                $resArr = str_split($res, 2);

                $json['stx'] = implode(array_slice($resArr, 0, 1));
                $json['ch'] = hexdec(implode(array_slice($resArr, 1, 4)));
                $json['ack'] = hexdec(implode(array_slice($resArr, 5, 1)));
                $json['ret'] = implode(array_slice($resArr, 6, 1));
                $json['len'] = hexdec(implode(array_slice($resArr, 7, 2)));
                $json['data'] = array_slice($resArr, 9, $json['len']);
                $json['crc'] = implode(array_slice($resArr, -2, 2));

                $output[] = $json;
            }
        } else { //type = 0, single response
            $resArr = str_split($response, 2);

            $json['stx'] = implode(array_slice($resArr, 0, 1));
            $json['ch'] = hexdec(implode(array_slice($resArr, 1, 4)));
            $json['ack'] = hexdec(implode(array_slice($resArr, 5, 1)));
            $json['ret'] = implode(array_slice($resArr, 6, 1));
            $json['len'] = hexdec(implode(array_slice($resArr, 7, 2)));
            $json['data'] = array_slice($resArr, 9, $json['len']);
            $json['crc'] = implode(array_slice($resArr, -2, 2));

            $output = $json;
        }


        return $output;
    }

    /**
     * Convert command, data and length to this format:
     * 
     * STX      CH(device code)     CMD(command)    LEN(data length)    DATA            CRC16
     * 0xA5     4 Bytes             1 Byte          2 Bytes             0-400 Bytes     2 Bytes
     * 
     * @param hex $command
     * @param string $data
     * @param int $len
     * @return string
     * @access private
     */
    private function buildRequest($command, $data = '', $len = -1) {
        $len = $len == -1 ? strlen($data) / 2 : $len;
        $req = sprintf("A5%08s%02x%04x%s", $this->id, $command, $len, $data);
        $req .= $this->crc16(hex2bin($req));

        return $req;
    }

    /**
     * Send commands to device and parse response/s
     * @param string|array $commands
     * @param int $type
     * @return parseResponse
     * @access private
     */
    private function request($commands, $type = 0) {

        //if commands are string convert them to an array
        $commands = is_array($commands) ? $commands : [$commands];

        //build request array
        $req = [
            'id' => $this->id,
            'port' => $this->port,
            'command' => $commands,
            'type' => $type
        ];

        //send request to gearman job server
        $res = $this->client->doNormal("Anviz", json_encode($req));

        //if type is 1 that means that we might receive multiple responses so we must decode them to array
        if ($type == 1) {
            $res = json_decode($res);
        }

        //parse all the responses we've got from device
        return $this->parseResponse($res, $type);
    }

    /**
     * Get the firmware version, communication password, sleep time, volume, language, date
     * and time format, attendance state, language setting flag, command version
     * @return array|false
     * @access public
     */
    public function getInfo1() {

        $commands = $this->buildRequest(0x30);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xB0) {
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

    /**
     * Set the communication password, sleep time, volume, language, date format, attendance state, and language setting flag.
     * @param string $pass
     * @param int|hex $sleep_time
     * @param int|hex $volume
     * @param int|hex $language
     * @param int|hex $dt_format
     * @param int|hex $attendance_state
     * @param int|hex $language_setting_flag
     * @return boolean
     * @access public
     */
    public function setInfo1($pass = 0xFFFFFF, $sleep_time = 0xFF, $volume = 0xFF, $language = 0xFF, $dt_format = 0xFF, $attendance_state = 0xFF, $language_setting_flag = 0xFF) {
        $reserved = 0x00;

        if (!$sleep_time || $sleep_time == '' || !is_numeric($sleep_time) || $sleep_time > 250 || $sleep_time < 0)
            $sleep_time = 0xFF;

        if (!$volume || $volume == '' || !is_numeric($volume) || $volume > 5 || $volume < 0)
            $volume = 0xFF;

        if (!$language || $language == '' || !is_numeric($language) || $language > 16 || $language < 0)
            $language = 0xFF;

        if (!$attendance_state || $attendance_state == '' || !is_numeric($attendance_state) || $attendance_state > 15 || $attendance_state < 0)
            $attendance_state = 0xFF;


        $data = sprintf("%06x%02x%02x%02x%02x%02x%02x%02x", $pass, $sleep_time, $volume, $language, $dt_format, $attendance_state, $language_setting_flag, $reserved);

        $commands = $this->buildRequest(0x31, $data);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xB1) {
            return true;
        }

        return false;
    }

    /**
     * Get the T&A device Compare Precision, Fixed Wiegand Head Code, Wiegand Option,
     * Work code permission, real-time mode setting, FP auto update setting, relay mode,
     * Lock delay, Memory full alarm, Repeat attendance delay, door sensor delay, scheduled
     * bell delay.
     * @return array|false
     * @access public
     */
    public function getInfo2() {
        $commands = $this->buildRequest(0x32);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xB2) {
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

    /**
     * Set the T&A device Compare Precision, Fixed Wiegand Head Code, Wiegand Option,
     * Work code permission, real-time mode setting, FP auto update setting, relay mode,
     * Lock delay, Memory full alarm, Repeat attendance delay, door sensor delay, scheduled
     * bell delay.
     * @return array|false
     * @access public
     */
    public function setInfo2() {
        // TO DO
    }

    /**
     * Get the date and time of T&A
     * @param string $format Date Time format. Default is 'Y-m-d H:i:s'
     * @return string|false
     * @access public
     */
    public function getDateTime($format = 'Y-m-d H:i:s') {
        $commands = $this->buildRequest(0x38);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xB8) {
            $data = $res['data'];

            foreach ($data as $key => $value) {
                $data[$key] = hexdec($value);
            }

            $date = sprintf('20%02d-%02d-%02d %02d:%02d:%02d', $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);

            $output = date($format, strtotime($date));

            return $output;
        }

        return false;
    }

    /**
     * Set the date and time of T&A
     * @param string $dateTime (optional) If not set, current datetime will be set
     * @return boolean
     * @access public
     */
    public function setDateTime($dateTime = '') {
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

        $commands = $this->buildRequest(0x39, $data);

        $res = $this->request($commands);


        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xB9) {
            return true;
        }

        return false;
    }

    /**
     * Get the IP address, subnet Mask, MAC address, Default gateway, Server IP address,Far limit, Com port NO., TCP/IP mode, DHCP limit.
     * @return array|boolean
     */
    public function getTCPIPParameters() {

        $commands = $this->buildRequest(0x3A);

        $res = $this->request($commands);


        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xBA) {

            $data = [
                'ip_address' => long2ip(hexdec(implode(array_slice($res['data'], 0, 4)))),
                'subnet_mask' => long2ip(hexdec(implode(array_slice($res['data'], 4, 4)))),
                'mac_address' => implode(array_slice($res['data'], 8, 6)),
                'default_gateway' => long2ip(hexdec(implode(array_slice($res['data'], 14, 4)))),
                'server_ip' => long2ip(hexdec(implode(array_slice($res['data'], 18, 4)))),
                'far_limit' => hexdec($res['data'][22]),
                'comm_port' => hexdec(implode(array_slice($res['data'], 23, 2))),
                'tcpip_mode' => hexdec($res['data'][25]),
                'dhcp_limit' => hexdec($res['data'][26])
            ];
            $output = $data;

            return $output;
        }

        return false;
    }

    function setTCPIPParameters() {
        //TODO
    }

    /**
     * Get record information, including the amount of Used User, Used FP, Used Password, Used Card, All Attendance Record, and New Record.
     * @return array|boolean
     * @access public
     */
    public function getRecordInformation() {

        $commands = $this->buildRequest(0x3C);

        $res = $this->request($commands, 0);

        $output = [];

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xBC) {

            $data = [
                'user_amount' => hexdec(implode(array_slice($res['data'], 0, 3))),
                'fp_amount' => hexdec(implode(array_slice($res['data'], 3, 3))),
                'password_amount' => hexdec(implode(array_slice($res['data'], 6, 3))),
                'card_amount' => hexdec(implode(array_slice($res['data'], 9, 3))),
                'all_record_amount' => hexdec(implode(array_slice($res['data'], 12, 3))),
                'new_record_amount' => hexdec(implode(array_slice($res['data'], 15, 3))),
            ];

            $output = $data;

            return $output;
        }

        return false;
    }

    /**
     * download record, the downloading max number is 25 each time.(record data length: 25*14 = 350Byte)
     * @param hex $type @see constant DOWNLOAD_*
     * @return array
     */
    public function downloadTARecords($type = PHPAnviz::DOWNLOAD_NEW) {
        $recordInfo = $this->getRecordInformation();

        if ($type == PHPAnviz::DOWNLOAD_NEW) {
            $maxRecords = $recordInfo['new_record_amount'];
            $deleteAmount = $maxRecords;
        } else {
            $maxRecords = $recordInfo['all_record_amount'];
            $deleteAmount = 0x00;
        }

        $num = min(25, $maxRecords);

        $data = sprintf("%02x%02x", $type, $num);

        $commands[0] = $this->buildRequest(0x40, $data);
        $maxRecords -= $num;

        while ($maxRecords > 0) {
            $num = min(25, $maxRecords);
            $data = sprintf("%02x%02x", 0, $num);

            $commands[] = $this->buildRequest(0x40, $data);
            $maxRecords -= $num;
        }

        $resArr = $this->request($commands, 1);

        $data = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xC0) {

                for ($i = 0; $i < hexdec($res['data'][0]); $i++) {
                    $event = [
                        'user_code' => hexdec(implode(array_slice($res['data'], $i * 14 + 1, 5))),
                        'datetime' => date('Y-m-d H:i:s', hexdec(implode(array_slice($res['data'], $i * 14 + 6, 4))) + PHPAnviz::ANVIZ_EPOCH),
                        'backup_code' => hexdec($res['data'][$i * 14 + 10]),
                        'record_type' => (int) substr(base_convert($res['data'][$i * 14 + 11], 16, 2), 1),
                        'work_type' => hexdec(implode(array_slice($res['data'], $i * 14 + 12, 2))),
                    ];

                    $data[] = $event;
                }

                if ($type == PHPAnviz::DOWNLOAD_NEW) {
                    $this->clearRecords(PHPAnviz::CLEAR_NEW_PARTIALY, $deleteAmount);
                }
            }
        }



        return $data;
    }

    /**
     * download staff information
     * @return array
     * @access public
     */
    public function downloadStaffInfo() {

        $recordInfo = $this->getRecordInformation();

        $maxUsers = $recordInfo['user_amount'];
        $num = min(8, $maxUsers);
        $data = sprintf("%02x%02x", 0x01, $num);
        $commands[0] = $this->buildRequest(0x72, $data);
        $maxUsers -= $num;

        while ($maxUsers > 0) {
            $num = min(8, $maxUsers);
            $data = sprintf("%02x%02x", 0x00, $num);
            $commands[] = $this->buildRequest(0x72, $data);
            $maxUsers -= $num;
        }

        $resArr = $this->request($commands, 1);

        $final = [];

        foreach ($resArr as $res) {
            if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xF2) {
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

    /**
     * Add 00 before every byte
     * @param string $name
     * @return string
     * @access private
     */
    private function fixName($name) {
        $i = 0;

        $newName = '';

        while (strlen($newName) < 40) {
            $newName .= '00' . $name[$i] . $name[$i + 1];
            $i += 2;
        }

        return $newName;
    }

    /**
     * upload staff information. If user data is empty, set it as 0xFF. For instance, card Id set as 0xFF if user don’t enroll card. 
     * FP enroll state can not set, this value is 0
     * @param array $users
     * @return boolean
     * @access public
     */
    function uploadStaffInfo($users) {

        $employees = [];

        foreach ($users as $user) {

            $name = unpack('H*', $user[3]);

            $name = $this->fixName($name[1]);

            $employee = [
                'user_id' => sprintf('%010x', $user[0]),
                'pwd' => sprintf('%06x', $user[1]),
                'card_id' => sprintf('%08x', $user[2]), //$user[2]);
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

        $commands = $this->buildRequest(0x73, $data);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xF3) {
            return true;
        }

        return false;
    }

    /**
     * Download FP Template from T&A device
     * @param int $user
     * @param hex $backup_code
     * @return FP Template|false
     * @access public
     */
    public function downloadFPTemplate($user, $backup_code = 0x01) {
        $data = sprintf('%010x%02x', $user, $backup_code);

        $commands = $this->buildRequest(0x44, $data);

        $res = $this->request($commands);

        $output = [];

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xC4) {
            return implode($res['data']);
        }

        return false;
    }

    /**
     * Get device ID which we set in device.
     * @return int|false
     * @access public
     */
    public function getDeviceId() {
        $commands = $this->buildRequest(0x74);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            $data = hexdec(implode($res['data']));

            return $data;
        }

        return false;
    }

    /**
     * Modify device ID in device menu
     * @param int $id
     * @return boolean
     * @access public
     */
    public function setDeviceId($id) {
        $data = sprintf('%08x', $id);

        $res = $this->request(75, $data);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS) {
            return true;
        }

        return false;
    }

    /**
     * Initialize all the user data area, clear all the staff info, FP data, password/card data
     * @return boolean
     * @access public
     */
    public function clearUsers() {

        $commands[] = $this->buildRequest(0x4D);


        $res = $this->request($commands);


        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xCD) {
            return true;
        }

        return false;
    }

    /**
     * Cancel all records, or cancel all/part new records sign.
     * @param hex $type @see const CLEAR_*
     * @param hex $amount
     * @return boolean
     * @access public
     */
    public function clearRecords($type = PHPAnviz::CLEAR_NEW, $amount = 0xFFFF) {

        $data = sprintf("%02x%04x", $type, $amount);

        $commands = $this->buildRequest(0x4E, $data);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xCE) {
            return true;
        }

        return false;
    }

    /**
     * Force T&A device output signal to open door without verifying user
     * @return boolean
     * @access public
     */
    public function openDoor() {
        $commands = $this->buildRequest(0x5E);

        $res = $this->request($commands);

        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xDE) {
            return true;
        }

        return false;
    }

    /**
     * Get T&A states from device
     * @return array | boolean
     * @access public
     */
    public function getTAStateTable() {
        $commands = $this->buildRequest(0x5A);

        $res = $this->request($commands);


        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xDA) {
            foreach ($res['data'] as $key => $value) {
                $res['data'][$key] = $value == 'FF' ? null : $this->hex2str($value);
            }

            return $res['data'];
        }

        return false;
    }

    /**
     * Set T&A state table. Max 16 different states. 
     * @param array $states
     * @return boolean
     * @access public
     */
    public function setTAStateTable($states) {
        
        //check if state element is empty or invalid and replace it with FF
        for ($i = 0; $i < 16; $i++) {
            if (!isset($states[$i]) || $states[$i] == '' || is_null($states[$i]) || $states[$i] == 'FF') {
                $states[$i] = 'FF';
            } else {
                $states[$i] = unpack('H*', $states[$i])[1];
            }
        }

        $commands = $this->buildRequest(0x5B, implode($states));

        $res = $this->request($commands);
        
        if ($res['ret'] == PHPAnviz::ACK_SUCCESS && $res['ack'] == 0xDB) {
            return true;
        }
        
        return false;
    }

}
