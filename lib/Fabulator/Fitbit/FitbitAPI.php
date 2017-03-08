<?php
namespace Fabulator\Fitbit;

class FitbitAPI extends FitbitAPIBase
{

    public function __construct($clientId, $secret)
    {
        parent::__construct($clientId, $secret);
    }

    public function requestAccessToken($code, $redirectUri, $expiresIn = null, $state = null)
    {
        $response = parent::requestAccessToken($code, $redirectUri, $expiresIn, $state);
        $decoded = json_decode($response->getBody(), true);
        $this->setToken($decoded['access_token']);
        return $decoded;
    }

    public function request($namespace, $method = 'GET', $data = [], $user = '-', $file = '.json')
    {
        json_decode(parent::send($namespace, $method, $data, $user, $file)->getBody(), true);
    }

    public function get($namespace, $user = '-', $file = '.json') {
        return $this->request($namespace, 'GET', [], $user, $file);
    }

    public function getActivity($type, \DateTime $from, \DateTime $to, $detail = '1min')
    {
        return $this->get('activities/' . $type . '/date/' . $from->format('Y-m-d') . '/' . $to->format('Y-m-d') . '/' . $detail . '/time/' . $from->format('H:i') . '/' . $to->format('H:i'));
    }

}
