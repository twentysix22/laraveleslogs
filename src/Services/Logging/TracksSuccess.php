<?php

namespace Twentysix22\LaravelESLogs\Services\Logging;

use Exception;

trait TracksSuccess
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * @var Exception
     */
    protected $exception;

    /**
     * Set the success.
     *
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * Get the success.
     *
     * @return bool|null
     */
    public function getSuccess(): ?bool
    {
        return $this->success;
    }

    /**
     * Set the exception.
     *
     * @param Exception|null $exception
     * @return $this
     */
    public function setException(?Exception $exception = null)
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Get the exception.
     *
     * @return Exception|null
     */
    public function getException(): ?Exception
    {
        return $this->exception;
    }

    /**
     * Get success data.
     *
     * @return array
     */
    public function getSuccessData(): array
    {
        return [
            'success'      => $this->success,
            'success_rate' => (int) $this->success,
            'exception'    => $this->exception ? $this->formatException($this->exception) : null,
        ];
    }

    /**
     * Format a given exception as an array.
     *
     * @param Exception $exception
     * @return array
     */
    protected function formatException(Exception $exception): array
    {
        return [
            'class'   => get_class($exception),
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ];
    }
}
