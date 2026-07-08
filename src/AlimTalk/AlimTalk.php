<?php

namespace Seungmun\Sens\AlimTalk;

use Exception;
use Illuminate\Support\Facades\Log;
use Seungmun\Sens\Exceptions\SensException;
use Seungmun\Sens\Sens;

class AlimTalk extends Sens
{
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->setServiceId($config['alimtalk_service_id']);
    }

    /**
     * @throws SensException
     */
    public function send(array $params): void
    {
        if (! $this->assertValidTokens()) {
            throw SensException::InvalidNCPTokens('NCP tokens are invalid.');
        }

        $uri = '{method} https://sens.apigw.ntruss.com/alimtalk/v2/services/{service}/messages';

        $endpoint = $this->resolveEndpoint($uri, [
            'method' => 'POST',
            'service' => $this->getServiceId(),
        ]);

        try {
            $this->httpClient()->post($endpoint['url'], [
                'headers' => $this->prepareRequestHeaders(
                    $endpoint['method'],
                    $endpoint['path']
                ),
                'body' => json_encode($params),
            ]);
        } catch (Exception $e) {
            Log::error('SENS AlimTalk send failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            throw new SensException($e);
        }
    }
}
