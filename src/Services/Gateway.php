<?php

namespace Shipu\Banglalink\Services;

use Apiz\AbstractApi;

class Gateway extends AbstractApi
{

    protected $prefix = 'sendSMS';

    protected $config = [];

    /**
     * Banglalink constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * set base URL for guzzle client
     *
     * @return string
     */
    protected function setBaseUrl()
    {
        return 'https://vas.banglalinkgsm.com';
    }

    /**
     * Getting response from api
     * @param array $params
     * @return mixed
     */
    public function sendMessage(array $params)
    {
        return $this->formParams($params)->post('sendSMS');
    }

}