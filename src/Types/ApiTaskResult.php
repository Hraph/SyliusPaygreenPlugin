<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Types;


class ApiTaskResult implements ApiTaskResultInterface
{
    private bool $isSuccess = true;

    private int $successCount = 0;

    private string $message = "";

    private ?object $data;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param bool $isSuccess
     */
    public function setIsSuccess(bool $isSuccess): void
    {
        $this->isSuccess = $isSuccess;
    }

    /**
     * @return int
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * @param int $successCount
     */
    public function setSuccessCount(int $successCount): void
    {
        $this->successCount = $successCount;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return object|null
     */
    public function getData(): ?object
    {
        return $this->data;
    }

    /**
     * @param object|null $data
     */
    public function setData(?object $data): void
    {
        $this->data = $data;
    }
}
