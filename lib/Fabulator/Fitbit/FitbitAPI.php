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
     * Send authorized request to Fitbit API.
     *
     * @param string $url called url
     * @param string $method http method
     * @param array $data data in body
     * @return array response from Fitbit API
     */
    public function request($url, $method = 'GET', $data = [])
    {
        return json_decode((string) parent::send($url, $method, $data)->getBody(), true);
    }

    /**
     * @param string $namespace
     * @param array $data
     * @param string $file file type
     * @return array
     */
    public function get($namespace, $data = [], $file = '.json')
    {
        return $this->request(self::FITBIT_API_URL . 'user/-/' . $namespace . $file . '?' . http_build_query($data));
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
                ->setDistance($activity['distance'])
                ->setHeartRateLink($activity['heartRateLink'])
                ->setAvgHeartRate($activity['averageHeartRate'])
                ->setStartTime(new \DateTime($activity['startTime']))
                ->setTcxLink($activity['tcxLink'])
                ->setWorkoutId($activity['logId'])
                ->setWorkoutTypeId($activity['activityTypeId']);
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

}
