<?php

namespace Fabulator\Fitbit\Exception;

class TooManyRequests extends \Exception {

    /**
     * @var \DateTime
     */
    private $limitResetIn;

    /**
     * TooManyRequests constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param \DateTime $limitResetIn
     */
    public function __construct($message, $code = 0, \Exception $previous = null, \DateTime $limitResetIn) {
        parent::__construct($message, $code, $previous);

        $this->limitResetIn = $limitResetIn;
    }

    /**
     * @return \DateTime
     */
    public function getLimitResetIn()
    {
        return $this->limitResetIn;
    }
}