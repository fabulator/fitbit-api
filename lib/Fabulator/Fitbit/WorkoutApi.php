<?php
namespace Fabulator\Fitbit;

trait WorkoutApi {
    /**
     * @param \Datetime $before
     * @param \Datetime $after
     * @param string $sort
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getWorkouts($before = null, $after = null, $offset = 0, $limit = 100, $sort = 'desc')
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
                ->setDuration(new \DateInterval('PT'. ($activity['duration'] / 1000) .'S'))
                ->setStartTime(new \DateTime($activity['startTime']))
                ->setId($activity['logId'])
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
}