<?php


namespace Hraph\SyliusPaygreenPlugin\Types;


class ApiConfig
{
    private string $username;

    private string $apiKey;

    /**
     * ApiConfig constructor.
     * @param string $username
     * @param string $apiKey
     */
    public function __construct(string $username, string $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

}
