<?php

namespace Seungmun\Sens;

use GuzzleHttp\Client;
use Seungmun\Sens\Contracts\Sens as SensContract;

abstract class Sens implements SensContract
{
    protected ?Client $http = null;

    private string $serviceId = '';

    private string $accessKey = '';

    private string $secretKey = '';

    protected array $config = [];

    private array $headers = [];

    /**
     * Create a new SENS instance.
     */
    public function __construct(array $config)
    {
        $this->httpClient();

        $this->setServiceId($config['service_id'])
            ->setAccessKey($config['access_key'])
            ->setSecretKey($config['secret_key']);

        $this->config = $config;
    }

    /**
     * Create a new HTTP Request Client.
     */
    protected function httpClient(): Client
    {
        return $this->http ?: $this->http = new Client;
    }

    /**
     * Determine if tokens are exists normally.
     */
    public function assertValidTokens(): bool
    {
        return ! empty($this->getServiceId()) &&
            ! empty($this->getAccessKey()) &&
            ! empty($this->getSecretKey());
    }

    /**
     * Get SENS service identifier.
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * Set SENS service identifier.
     *
     * @return Sens
     */
    public function setServiceId(string $serviceId): static
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get SENS access key.
     */
    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    /**
     * Set SENS access key.
     *
     * @return Sens
     */
    public function setAccessKey(string $accessKey): static
    {
        $this->accessKey = $accessKey;

        return $this;
    }

    /**
     * Get SENS secret key.
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * Set SENS secret key.
     *
     * @return Sens
     */
    public function setSecretKey(string $secretKey): static
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Resolve the given uri to http request url.
     */
    public function resolveEndpoint(string $uri, array $params): array
    {
        foreach ($params as $key => $value) {
            $uri = str_replace('{'.$key.'}', $value, $uri);
        }

        $tokens = explode(' ', $uri);

        return [
            'method' => $tokens[0],
            'url' => $tokens[1],
            'path' => parse_url($tokens[1], PHP_URL_PATH),
            'host' => parse_url($tokens[1], PHP_URL_HOST),
        ];
    }

    /**
     * Prepare HTTP headers for request NCLOUD API v2 authentication.
     */
    public function prepareRequestHeaders(string $method, string $uri): array
    {
        $timestamp = $this->timestamp();

        $this->addHeader('Content-Type', 'application/json; charset=utf-8');
        $this->addHeader(self::X_NCP_APIGW_TIMESTAMP, $timestamp);
        $this->addHeader(self::X_NCP_IAM_ACCESS_KEY, $this->getAccessKey());
        $this->addHeader(self::X_NCP_APIGW_SIGNATURE_V2, $this->makeSignature($method, $uri, $timestamp));

        return $this->headers();
    }

    /**
     * Get current timestamp to compare api server.
     */
    protected function timestamp(): string
    {
        return strval((int) round(microtime(true) * 1000));
    }

    /**
     * Add a new HTTP header attribute.
     *
     * @return $this
     */
    public function addHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * generate x-ncp-apigw-signature-v2 token for authentication.
     */
    public function makeSignature(string $method, string $uri, string $timestamp): string
    {
        $buffer = [];

        // Important - do not change these all lines down here ever!
        $buffer[] = strtoupper($method).' '.$uri;
        $buffer[] = $timestamp;
        $buffer[] = $this->getAccessKey();

        $hash = hex2bin(hash_hmac('sha256', implode("\n", $buffer), $this->getSecretKey()));

        return base64_encode($hash);
    }

    /**
     * HTTP Header Attributes
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Remove the given HTTP header.
     *
     * @return Sens
     */
    public function removeHeader(string $key): static
    {
        unset($this->headers[$key]);

        return $this;
    }
}
