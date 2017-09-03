<?php
namespace Fabulator\Fitbit;

use Fabulator\Fitbit\Exception\TooManyRequests;
use Fabulator\Fitbit\Exception\FitbitApiException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Class FitbitAPI
 * @package Fabulator\Fitbit
 */
class FitbitApi extends FitbitApiBase
{
    use IntradayApi;
    use SleepApi;

    const DATE_FORMAT = 'Y-m-d';
    const TIME_FORMAT = 'H:i';
    const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * @var null|int
     */
    private $remainingRequests = null;

    /**
     * @var null|\DateTime
     */
    private $limitResetIn = null;

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
     * @return int|null
     */
    public function getRemainingRequests()
    {
        return $this->remainingRequests;
    }

    /**
     * @param int $remainingRequests
     */
    public function setRemainingRequests($remainingRequests)
    {
        $this->remainingRequests = $remainingRequests;
    }

    /**
     * @return \DateTime|null
     */
    public function getRequestLimitResetIn()
    {
        return $this->limitResetIn;
    }

    /**
     * @param \DateTime $resetIn
     */
    public function setRequestLimitResetIn(\DateTime $resetIn)
    {
        $this->limitResetIn = $resetIn;
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
     * @throws FitbitApiException
     */
    public function refreshAccessToken($refreshToken, $expiresIn = null) {
        try {
            $response = parent::refreshAccessToken($refreshToken, $expiresIn);
        } catch (ClientException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            throw new FitbitApiException($response['errors'], $e->getCode(), $e);
        }
        $decoded = json_decode($response->getBody(), true);
        $this->setToken($decoded['access_token']);
        return $decoded;
    }

    /**
     * Set api limit to object from response
     *
     * @param ResponseInterface $response
     */
    private function setLimits(ResponseInterface $response)
    {
        $haveLimits = isset($response->getHeaders()['Fitbit-Rate-Limit-Remaining']);
        if ($haveLimits) {
            $this->setRemainingRequests((int) $response->getHeaders()['Fitbit-Rate-Limit-Remaining'][0]);
            $this->setRequestLimitResetIn((new \DateTime())->add(new \DateInterval('PT' . $response->getHeaders()['Fitbit-Rate-Limit-Reset'][0] . 'S')));
        }
    }

    /**
     * Send authorized request to Fitbit API.
     *
     * @param string $url called url
     * @param string $method http method
     * @param array $data data in body
     * @return array|string response from Fitbit API
     * @throws TooManyRequests When api limit is reached
     * @throws FitbitApiException when api request failed
     */
    public function request($url, $method = 'GET', $data = [])
    {
        try {
            $response = parent::send($url, $method, $data);
        } catch (ClientException $e) {
            $this->setLimits($e->getResponse());
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            if ($response['errors'][0]['message'] === 'Too Many Requests') {
                throw new TooManyRequests('Too many requests. Limit will reset in ' . $this->getRequestLimitResetIn()->format('c') . '.', $e->getCode(), $e, $this->getRequestLimitResetIn());
            }

            throw new FitbitApiException($response['errors'], $e->getCode(), $e);
        } catch(ServerException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            throw new FitbitApiException($response['errors'], $e->getCode(), $e);
        }

        $this->setLimits($response);
        if ($response->getHeaders()['Content-Type'][0] === 'application/json;charset=UTF-8') {
            return json_decode((string) $response->getBody(), true);
        }
        return (string) $response->getBody();
    }

    /**
     * @param string $namespace
     * @param array $data
     * @param string $version
     * @param string $file file type
     * @return array
     */
    public function get($namespace, $data = [], $version = '1', $file = '.json')
    {
        return $this->request(self::FITBIT_API_URL . $version . '/user/-/' . $namespace . $file . '?' . http_build_query($data));
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
            $data['afterDate'] = $after->format(self::DATE_TIME_FORMAT);
        }

        if ($before !== null) {
            $data['beforeDate'] = $before->format(self::DATE_TIME_FORMAT);
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

        if (!$workout) {
            return null;
        }

        if ($workout->getStart() > $from) {
            return $workout;
        }

        return null;
    }

    public function addWorkout(\DateTime $date, $activityTypeId, $durationInSec, $distance = null, $calories = null, $distanceUnit = null)
    {
        $data = [
            'date' => $date->format(self::DATE_FORMAT),
            'startTime' => $date->format(self::TIME_FORMAT),
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

    public function getProfile()
    {
        return $this->get('profile');
    }

}
