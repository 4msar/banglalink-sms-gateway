<?php

namespace Shipu\Banglalink;

use Shipu\Banglalink\Services\Gateway;

class SMS
{
    protected $sms = [];

    protected $mobiles = null;

    protected $config;

    protected $debug = false;

    protected $template = true;

    protected $sender = null;

    protected $autoParse = false;

    protected $responseDetails = false;

    protected $numberPrefix = '88';

    protected $prefix = 'sendSMS';

    protected $sendingUrl = '/sendSMS';


    protected $sendingParameters = [];


    protected $code = '88';

    protected $body = '';

    protected $gateway;

    /**
     * Prepare Sending parameters
     *
     * @param $sms
     * @param $mobiles
     *
     * @return $this
     */
    private function gettingParameters( $sms, $mobiles )
    {
        $this->sendingParameters = [
            'userID'  => $this->config[ 'user_id' ],
            'passwd'  => $this->config[ 'password' ],
            'sender'  => $this->sender,
            'message' => $sms,
            'msisdn'  => $mobiles,
        ];

        return $this;
    }

    /**
     * Getting response from api
     *
     * @return mixed
     */
    private function sendToServer()
    {
        if ( $this->debug ) {
            return $this->sendingParameters;
        } elseif ( $this->autoParse && !$this->responseDetails ) {
            return $this->query($this->sendingParameters)->get($this->sendingUrl)->autoParse();
        } elseif ( $this->responseDetails ) {
            $response = $this->query($this->sendingParameters)->get($this->sendingUrl)->autoParse();
            preg_match_all('!\d+!', $response, $result);

            return [
                'success' => $result[ 0 ][ 0 ],
                'failed'  => $result[ 0 ][ 1 ]
            ];
        }

        return $this->query($this->sendingParameters)->get($this->sendingUrl);
    }

    /**
     * Sending Multiple SMS
     *
     * @return array
     */
    protected function makeMultiSmsMultiUser()
    {
        $response = [];
        $count    = 1;
        if ( is_array($this->sms) ) {
            foreach ( $this->sms as $key => $message ) {
                if ( isset($this->mobiles[ $key ]) ) {
                    $number = $this->numberPrefix . $this->mobiles[ $key ];
                    $this->gettingParameters($message, $number);
                    $response[ 'res-' . $count++ . '-' . $number ] = $this->sendToServer();
                }
            }
        }

        return $response;
    }

    /**
     * Set Sender Details
     *
     * @param $sender
     * @return $this
     */
    public function sender( $sender )
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Set Debug
     *
     * @param bool $debug
     *
     * @return $this
     */
    public function debug( $debug = true )
    {
        $this->debug = $debug;

        return $this;
    }



    /**
     * Set Response Details
     *
     * @param bool $responseDetails
     * @return $this
     */
    public function details( $responseDetails = true )
    {
        $this->responseDetails = $responseDetails;

        return $this;
    }


    /**
     * Refactor
     * //TODO: Refactoring
     */



    /**
     * Banglalink constructor.
     *
     * @param $config
     * @param Gateway $gateway
     */
    public function __construct( $config )
    {
        $this->config = $config;

        $this->gateway = new Gateway($config);

    }

    public function to( $number )
    {
        if (is_array($number)) {
            $this->mobiles = $number;
        } else {
            $this->mobiles[] = $number;
        }


        return $this;
    }

    public function message($body, $number = null)
    {

        $this->body = $body;

        $this->mobiles = $number;

        return $this;
    }

    public function send($number = null)
    {
        if (!is_null($number)) {
            $this->mobiles = $number;
        }

        if (is_string($this->body) && is_array($this->mobiles) && count($this->mobiles[0]) == 2) {
            $body = $this->body;
            $this->body = [];
            foreach ($this->mobiles as $payload) {
                $this->body[$payload[0]] = vsprintf($body, $payload[1]);
            }
        }

        if (is_array($this->body)) {
            foreach ($this->body as $num => $body) {
                var_dump($this->sendMessage($body, $num));
            }
        } else {
            return $this->sendMessage($this->body, $this->mobiles);
        }

    }

    protected function sendMessage($body, $mobiles)
    {
        $params = $this->makePayload($body, $mobiles, $this->sender);

        return $this->gateway->sendMessage($params);
    }



    public function countryCode($code)
    {
        $this->code = $code;
    }


    protected function makePayload($message, $number, $sender = null)
    {

        $param = [
            'userID'  => $this->config[ 'user_id' ],
            'passwd'  => $this->config[ 'password' ],
            'message' => $message,
            'msisdn'  => $this->makeNumberSendable($number)
        ];

        if (!is_null($sender)) {
            $param['sender'] = $sender;
        }

        return $param;

    }


    protected function makeNumberSendable($number)
    {
        $number = $this->appendCountryCode($number);

        if (is_array($number)) {
            return implode(',', $number);
        }

        return $number;
    }


    protected function appendCountryCode($number)
    {
        $transform = [];

        if (is_array($number)) {
            foreach($number as $num) {
                $transform[] = $this->code . $num;
            }

            return $transform;
        }

        return $this->code . $number;
    }

}