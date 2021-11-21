<?php

namespace App\Services\Lexoffice;

use App\Exceptions\LexofficeException;
use App\Services\Lexoffice\Endpoints\FilesEndpoint;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Connector
{
    protected const LEXOFFICE_API_URL = 'https://api.lexoffice.io/v1';

    protected array $acceptableStatusCodes = [];

    public function __construct(
        protected bool $lexofficeIsAvailable = false,
        protected ?string $lexofficeAccessToken = null,
        protected ?Response $response = null,
        protected int $page = 0,
        protected int $pageSize = 25
    ) {
        $this->getAccessTokenFromConfig();
    }

    private function getAccessTokenFromConfig() {
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

        if(!$this instanceof FilesEndpoint) {
            $query['page'] = $this->page;

            $query['size'] = $this->pageSize;
        }

        $request = $this->prepareRequest();

        if($this instanceof FilesEndpoint) {
            $request->withHeaders(['accept' => 'application/pdf']);
        }

        $this->response = $request->get(self::LEXOFFICE_API_URL.$endpoint, Arr::query($query));

        return $this->processResponse();
    }

    protected function postRequest(string $endpoint, array $data) {
        if(!Str::startsWith($endpoint, '/')) {
            $endpoint = '/' . $endpoint;
        }

        $this->response = $this->prepareRequest()
            ->post(self::LEXOFFICE_API_URL.$endpoint, $data);

        return $this->processResponse();
    }

    protected function putRequest(string $endpoint, string $resourceId, array $data) {
        if(!Str::startsWith($endpoint, '/')) {
            $endpoint = '/' . $endpoint;
        }

        $endpoint = $endpoint.'/'.$resourceId;

        $this->response = $this->prepareRequest()->put(self::LEXOFFICE_API_URL.$endpoint, $data);

        return $this->processResponse();
    }

    private function prepareRequest() {
        return Http::withToken($this->lexofficeAccessToken)
            ->withHeaders([
                'accept' => 'application/json'
            ]);
    }

    private function processResponse() : object {
        if($this->response->status() > 299 && !in_array($this->response->status(), $this->acceptableStatusCodes)) {
            Log::warning(json_encode($this->response->object()));
            throw new LexofficeException(json_encode($this->response->object()));
        }

        if($this instanceof FilesEndpoint) {
            $contentDisposition = explode('=', $this->response->header('Content-Disposition'));

            dd($this->response);

            Storage::putFileAs(
                'invoices',
                '',
                Arr::last($contentDisposition)
            );
        }

        return $this->response->object();
    }
}
