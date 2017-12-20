<?php
namespace Fabulator\Fitbit;

trait IntradayApi {
    /**
     * @param string $type - one of the IntradayResource const
     * @param \DateTime $from - day or daytime to fetch
     * @param \DateTime|null $to - time till to fetch
     * @param string $detail - detail level - 1min or 15min (or 1sec for heart)
     * @return array
     */
    public function getIntradayActivity($type, \DateTime $from, \DateTime $to = null, $detail = '1min')
    {
        // If end time is not specified, set it to rest of the day
        if (!$to) {
            $to = clone $from;
            $to->setTime(23, 59, 59);
        }
        // fetch intraday data from Fitbit
        $response = $this->get('activities/' . $type . '/date/' . $from->format(self::DATE_FORMAT) . '/' . $to->format(self::DATE_FORMAT) . '/' . $detail . '/time/' . $from->format(self::TIME_FORMAT) . '/' . $to->format(self::TIME_FORMAT));

        // use datetime class for times
        $sets = [];
        foreach($response['activities-' . $type . '-intraday']['dataset'] as $data) {
            $time = new \DateTime($data['time']);
            $datetime = clone $from;
            $datetime->setTime((int) $time->format('G'), (int) $time->format('i'), (int) $time->format('s'));
            $sets[] = [
                'datetime' => $datetime,
                'value' => $data['value'],
            ];
        }

        return [
            'total' => (int) $response['activities-' . $type][0]['value'],
            'sets' => $sets,
            'source' => $response,
        ];
    }

    /**
     * @param \DateTime $from - day or daytime to fetch
     * @param \DateTime|null $to - time till to fetch
     * @param string $detail - detail level - 1min or 15min
     * @return array
     */
    public function getIntradaySteps(\DateTime $from, \DateTime $to = null, $detail = '1min')
    {
        return $this->getIntradayActivity(IntradayResource::STEPS, $from, $to, $detail);
    }

    /**
     * @param \DateTime $from - day or daytime to fetch
     * @param \DateTime|null $to - time till to fetch
     * @param string $detail - detail level - 1min or 15min
     * @return array
     */
    public function getIntradayHeart(\DateTime $from, \DateTime $to = null, $detail = '1min')
    {
        return $this->getIntradayActivity(IntradayResource::HEART, $from, $to, $detail);
    }
}