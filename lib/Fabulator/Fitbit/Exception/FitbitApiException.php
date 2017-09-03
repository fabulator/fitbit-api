<?php

namespace Fabulator\Fitbit\Exception;

class FitbitApiException extends \Exception {
    /**
     * @var \array
     */
    private $data;

    /**
     * FitbitApiException constructor.
     * @param array $data
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($data, $code = 0, \Exception $previous = null) {
        parent::__construct($data[0]['message'], $code, $previous);

        $this->data = $data;
    }

    public function getErrorType() {
        return $this->data[0]->errorType;
    }
}