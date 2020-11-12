<?php

declare(strict_types=1);

namespace Hraph\SyliusPaygreenPlugin\Types;


interface ApiTaskResultInterface
{
    public function isSuccess(): bool;

    public function setIsSuccess(bool $success): void;

    public function getSuccessCount(): int;

    public function setSuccessCount(int $successCount): void;

    public function getMessage(): string;

    public function setMessage(string $message): void;

    public function getData(): ?object;

    public function setData(?object $object): void;

}
