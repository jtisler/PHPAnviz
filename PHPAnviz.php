<?php

class PHPAnviz {

    const ACK_SUCCESS = 0x00;
    const ACK_FAIL = 0x01;

    private $id;
    private $port;
    private $client;

    function __construct($id, $port) {
        $port = substr($port, 0, 1) != ":" ? ":" . $port : $port;

        $this->id = $id;
        $this->port = $port;

        $this->client = new GearmanClient();
        $this->client->addServer("jerko.novi-net.net");
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

    function getDateTime($format = 'Y-m-d H:i:s') {
        $req = [
            'id' => $this->id,
            'port' => $this->port,
            'command' => '38',
            'data' => '',
            'length' => ''
        ];

        $res = $this->client->doNormal("Anviz", json_encode($req));

        $res = $this->parseResponse($res);

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

}
