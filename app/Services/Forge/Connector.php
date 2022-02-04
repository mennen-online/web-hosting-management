<?php

namespace App\Services\Forge;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Connector
{
    protected static $url = 'https://forge.laravel.com/api/v1';

    protected ?string $token = null;

    public function __construct()
    {
        $this->token = config('forge.token');
    }

    public function isForgeAvailable(): bool
    {
        return $this->token !== null;
    }

    private function prepareRequest(): PendingRequest
    {
        return Http::withHeaders(
            [
                'accept' => 'application/json',
                'content-type' => 'application/json'
            ]
        )->withToken($this->token);
    }

    protected function getRequest(string $endpoint, int|array|null $id = null)
    {
        if (is_int($id)) {
            $endpoint = $this->prepareEndpointUrl($endpoint, $id);
        } elseif (is_array($id)) {
            $endpoint = $this->prepareEndpointUrl($endpoint) . '?' . Arr::query($id);
        }
        return $this->prepareRequest()->get(self::$url . $endpoint);
    }

    protected function postRequest(string $endpoint, array|null $params = null)
    {
        $endpoint = $this->prepareEndpointUrl($endpoint);
        return $this->prepareRequest()->post(self::$url . $endpoint, $params ?? []);
    }

    protected function putRequest(string $endpoint, int $id, array $params)
    {
        $endpoint = $this->prepareEndpointUrl($endpoint, $id);
        return $this->prepareRequest()->put(self::$url . $endpoint, $params);
    }

    protected function deleteRequest(string $endpoint, int|array|null $id = null)
    {
        if (is_int($id)) {
            $endpoint = $this->prepareEndpointUrl($endpoint, $id);
        }
        return $this->prepareRequest()->delete(self::$url . $endpoint);
    }

    /**
     * @param string $endpoint
     * @param int|null $id
     * @return string
     */
    private function prepareEndpointUrl(string $endpoint, ?int $id = null): string
    {
        if (!Str::startsWith($endpoint, '/')) {
            $endpoint = '/' . $endpoint;
        }
        if ($id !== null) {
            $endpoint = Str::endsWith($endpoint, '/') ? $endpoint . $id : $endpoint . '/' . $id;
        }
        return $endpoint;
    }
}
