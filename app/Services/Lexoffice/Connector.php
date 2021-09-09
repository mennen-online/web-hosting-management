<?php

namespace App\Services\Lexoffice;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Connector
{
    protected const LEXOFFICE_API_URL = 'https://api.lexoffice.io/v1';

    protected bool $lexofficeIsAvailable = false;

    protected int $page = 0;

    protected int $pageSize = 25;

    public function __construct(
        protected ?string $lexofficeAccessToken = null
    ) {
        $this->lexofficeAccessToken = config('lexoffice.access_token');

        if($this->lexofficeAccessToken === null) {
            Log::info('Lexoffice Access Token is not Provided.');
        }
    }

    public function isLexofficeAvailable() : bool {
        return $this->lexofficeAccessToken !== null;
    }

    public function setPage(int $page) {
        $this->page = $page;

        return $this;
    }

    public function setPageSize(int $pageSize) {
        $this->pageSize = $pageSize;

        return $this;
    }

    protected function getRequest(string $endpoint, array $query = []) {
        if(!Str::startsWith($endpoint, '/')) {
            $endpoint = '/' . $endpoint;
        }

        $query['page'] = $this->page;

        $query['size'] = $this->pageSize;

        return Http::withToken($this->lexofficeAccessToken)
            ->withHeaders([
                'accept' => 'application/json'
            ])
            ->get(self::LEXOFFICE_API_URL.$endpoint, Arr::query($query));
    }
}
