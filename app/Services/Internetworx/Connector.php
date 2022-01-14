<?php

namespace App\Services\Internetworx;

use App\Exceptions\InternetworxException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use INWX\Domrobot;

/**
 *
 */
class Connector
{
    /**
     * @throws BindingResolutionException
     */
    public function __construct(
        protected Domrobot $domrobot
    ) {
        $this->domrobot = app()->make(Domrobot::class);

        $this->domrobot->setLanguage('de')->useJson();
        if (config('app.env') !== 'production') {
            $this->domrobot->useOte()->setDebug(false);
        } else {
            $this->domrobot->useLive();
        }
    }

    /**
     * @return bool
     */
    public function isOte()
    {
        return $this->domrobot->isOte();
    }

    /**
     * @return void
     */
    protected function prepareRequest()
    {
        if (config('internetworx.username') === null || config('internetworx.password') === null) {
            Log::warning('Please provide Internetworx Credentials');
        } else {
            $this->domrobot->login(
                config('internetworx.username'),
                config('internetworx.password')
            );
        }
    }

    /**
     * @param array $response
     * @param string $object
     * @return Collection|string
     */
    protected function processResponse(array $response, string $object): Collection|string
    {
        $code = Arr::get($response, 'code');
        if (!Arr::has($response, 'resData.' . $object) && !Str::is([1000, 1001], $code)) {
            try {
                Log::error('Internetworx Error');
                Log::error($code);
                Log::error(json_encode($response));
            } catch (BindingResolutionException $bindingResolutionException) {
                if ($code === 2003) {
                    throw new InternetworxException('Internetworx Validation Error - Please Contact us');
                }
                return collect();
            }
        }

        $result = Arr::get($response, 'resData.' . $object);

        if ($result) {
            if (is_array($result)) {
                return collect($result);
            }
            return $result;
        }
        return collect();
    }
}
