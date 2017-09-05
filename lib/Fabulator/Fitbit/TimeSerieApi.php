<?php
namespace Fabulator\Fitbit;

trait TimeSerieApi {
    public function getTImeSerie($type, \DateTime $from, \DateTime $to)
    {
        return $this->get('activities/' . $type . '/date/' . $from->format(self::DATE_FORMAT) . '/' . $to->format(self::DATE_FORMAT));
    }

    public function getRestingHeartRate(\DateTime $from, \DateTime $to)
    {
        $response = $this->getTImeSerie(IntradayResource::HEART, $from, $to);

        $items = [];

        foreach($response['activities-heart'] as $item) {
            $items[] = [
                'datetime' => new \DateTime($item['dateTime']),
                'value' => isset($item['value']['restingHeartRate']) ? $item['value']['restingHeartRate'] : null,
            ];
        }

        return $items;
    }
}