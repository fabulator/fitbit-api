<?php
namespace Fabulator\Fitbit;

/**
 * Class FitbitAPI
 * @package Fabulator\Fitbit
 */
class FitbitApi extends FitbitApiBase
{

    /**
     * FitbitAPI constructor.
     * @param string $clientId
     * @param string $secret
     */
    public function __construct($clientId, $secret)
    {
        parent::__construct($clientId, $secret);
    }

    /**
     * Request new Fitbit access token.
     *
     * @param string $code code from Fitbit
     * @param string $redirectUri redirect uri used to get code
     * @param int|null $expiresIn set length of token
     * @param string|null $state This parameter will be added to the redirect URI exactly as your application specifies.
     * @return array response from Fitbit API
     */
    public function requestAccessToken($code, $redirectUri, $expiresIn = null, $state = null)
    {
        $response = parent::requestAccessToken($code, $redirectUri, $expiresIn, $state);
        $decoded = json_decode($response->getBody(), true);
        $this->setToken($decoded['access_token']);
        return $decoded;
    }

    /**
     * Refresh Fitbit token.
     *
     * @param string $refreshToken refresh token
     * @param int|null $expiresIn set length of token
     * @return array response from Fitbit API
     */
    public function refreshAccessToken($refreshToken, $expiresIn = null) {
        $response = parent::refreshAccessToken($refreshToken, $expiresIn);
        $decoded = json_decode($response->getBody(), true);
        $this->setToken($decoded['access_token']);
        return $decoded;
    }

    /**
     * Send authorized request to Fitbit API.
     *
     * @param string $url called url
     * @param string $method http method
     * @param array $data data in body
     * @return array|string response from Fitbit API
     */
    public function request($url, $method = 'GET', $data = [])
    {
        $response = parent::send($url, $method, $data);
        if ($response->getHeaders()['Content-Type'][0] === 'application/json;charset=UTF-8') {
            return json_decode((string) $response->getBody(), true);
        }
        return (string) $response->getBody();
    }

    /**
     * @param string $namespace
     * @param array $data
     * @param string $file file type
     * @return array
     */
    public function get($namespace, $data = [], $file = '.json')
    {
        return $this->request(self::FITBIT_API_URL . '1/user/-/' . $namespace . $file . '?' . http_build_query($data));
    }

    /**
     * @param $namespace
     * @param array $data
     * @return array response from Fitbit
     */
    public function post($namespace, $data = [])
    {
        return $this->request(self::FITBIT_API_URL . '1/user/-/' . $namespace . '.json', 'POST', $data);
    }

    /**
     * @param \Datetime $before
     * @param \Datetime $after
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getWorkouts($before = null, $after = null, $sort = 'desc', $limit = 10, $offset = 0)
    {
        $data = [
            'sort' => $sort,
            'offset' => $offset,
            'limit' => $limit
        ];

        if ($after !== null) {
            $data['afterDate'] = $after->format('Y-m-d') . 'T' . $after->format('H:i:s');
        }

        if ($before !== null) {
            $data['beforeDate'] = $before->format('Y-m-d') . 'T' . $before->format('H:i:s');
        }

        $response = $this->get('activities/list', $data);
        $workouts = [];
        foreach($response['activities'] as $activity) {
            $workout = new Workout();
            $workout
                ->setSource($activity)
                ->setDuration((int) ($activity['duration'] / 1000))
                ->setStartTime(new \DateTime($activity['startTime']))
                ->setWorkoutId($activity['logId'])
                ->setWorkoutTypeId($activity['activityTypeId']);

            if (isset($activity['distance'])) {
                $workout->setDistance($activity['distance']);
            }

            if (isset($activity['heartRateLink'])) {
                $workout->setHeartRateLink($activity['heartRateLink']);
            }

            if (isset($activity['averageHeartRate'])) {
                $workout->setAvgHeartRate($activity['averageHeartRate']);
            }

            if (isset($activity['tcxLink'])) {
                $workout->setTcxLink($activity['tcxLink']);
            }

            $workouts[] = $workout;
        }
        return [
            'workouts' => $workouts,
            'pagination' => $response['pagination'],
        ];
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Workout|null
     */
    public function getWorkoutBetweenDate(\DateTime $from, \DateTime $to)
    {
        $response = $this->getWorkouts($to, null, 'desc', 1);

        /* @var $workout Workout */
        $workout = $response['workouts'][0];

        if ($workout->getStart() > $from) {
            return $workout;
        }

        return null;
    }

    public function addWorkout(\DateTime $date, $activityTypeId, $durationInSec, $distance = null, $calories = null, $distanceUnit = null)
    {
        $data = [
            'date' => $date->format('Y-m-d'),
            'startTime' => $date->format('H:i'),
            'activityId' => $activityTypeId,
            'durationMillis' => $durationInSec * 1000
        ];

        if ($calories !== null) {
            $data['manualCalories'] = (int) $calories;
        }

        if ($distance !== null) {
            $data['distance'] = $distance;
        }

        if ($distanceUnit !== null) {
            $data['distanceUnit'] = $distanceUnit;
        }

        return $this->post('activities', $data);
    }

    /**
     * @param $type
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $detail
     * @return array
     */
    public function getIntradayActivity($type, \DateTime $from, \DateTime $to, $detail = '1min')
    {
        return $this->get('activities/' . $type . '/date/' . $from->format('Y-m-d') . '/' . $to->format('Y-m-d') . '/' . $detail . '/time/' . $from->format('H:i') . '/' . $to->format('H:i'));
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string $detail
     * @return array
     */
    public function getHeartActivity(\DateTime $from, \DateTime $to, $detail = '1sec')
    {
        print_r($from);
        print_r($to);
        return $this->getIntradayActivity('heart', $from, $to, $detail);
    }

}
