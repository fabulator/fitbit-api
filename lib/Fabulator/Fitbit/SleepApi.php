<?php
namespace Fabulator\Fitbit;

trait SleepApi {
    /**
     * Get sleeps for give range.
     *
     * @param \DateTime $from
     * @param \DateTime|null $to
     * @return array
     */
    public function getSleeps(\DateTime $from, \DateTime $to = null)
    {
        return $this->get('sleep/date/' . $from->format(self::DATE_FORMAT) . ($to ? '/' . $to->format(self::DATE_FORMAT) : ''), [], '1.2');
    }

    /**
     * Get pagginated sleep data
     *
     * @param \DateTime $date
     * @param string $dateType afterDate or beforeDate
     * @param int $offset
     * @param int $limit
     * @param string $sort
     * @return array
     */
    public function getPaginatedSleeps(
        \DateTime $date,
        $dateType = 'afterDate',
        $offset = 0,
        $limit = 100,
        $sort = 'asc'
    ) {
        $options = [
            $dateType => $date->format(self::DATE_TIME_FORMAT),
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
        ];

        return $this->get('sleep/list', $options, '1.2');
    }
}