<?php
namespace Fabulator\Fitbit;

trait BodyApi {
    public function getWeight(\DateTime $from, \DateTime $to) {
        $response = $this->get('body/log/weight/date/' . $from->format(self::DATE_FORMAT) . '/' . $to->format(self::DATE_FORMAT));

        $toRespond = [];

        foreach($response['weight'] as $item) {
            $toRespond[] = [
                'bmi' => $item['bmi'],
                'fat' => $item['fat'],
                'weight' => $item['weight'],
                'datetime' => new \DateTime($item['date'] . ' ' . $item['time']),
                'source' => $item,
            ];
        }

        return $toRespond;
    }
}