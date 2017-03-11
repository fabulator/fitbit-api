<?php

namespace Fabulator\Fitbit;

/**
 * Class Workout
 */
class Workout {

    /**
     * @var string
     */
    private $workoutTypeId;

    /**
     * @var float
     */
    private $distance;

    /**
     * @var integer
     */
    private $duration;

    /**
     * @var string
     */
    private $heartRateLink;

    /**
     * @var integer
     */
    private $avgHeartRate;

    /**
     * @var string
     */
    private $workoutId;

    /**
     * @var \DateTime
     */
    private $start;

    /**
     * @var string
     */
    private $tcxLink;

    /**
     * @var array
     */
    private $source = [];

    /**
     * Workout constructor.
     */
    function __construct() { }

    /**
     * @param $id string
     * @return $this
     */
    public function setWorkoutTypeId($id)
    {
        $this->workoutTypeId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkoutTypeId()
    {
        return $this->workoutTypeId;
    }

    /**
     * @param $distance float
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

    /**
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param $duration integer
     * @return $this
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param $link string
     * @return $this
     */
    public function setHeartRateLink($link)
    {
        $this->heartRateLink = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeartRateLink()
    {
        return $this->heartRateLink;
    }

    /**
     * @param $id string
     * @return $this
     */
    public function setWorkoutId($id)
    {
        $this->workoutId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkoutId()
    {
        return $this->workoutId;
    }

    /**
     * @param \DateTime $start
     * @return $this
     */
    public function setStartTime(\DateTime $start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param $link string
     * @return $this
     */
    public function setTcxLink($link)
    {
        $this->tcxLink = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getTcxLink()
    {
        return $this->tcxLink;
    }

    /**
     * @param $source array
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return array
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param $hr int
     * @return $this
     */
    public function setAvgHeartRate($hr)
    {
        $this->avgHeartRate = $hr;
        return $this;
    }

    /**
     * @return int
     */
    public function getAvgHeartRate()
    {
        return $this->avgHeartRate;
    }

}