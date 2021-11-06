<?php

namespace App\Services\Internetworx;

use App\Exceptions\InternetworxException;
use Arr;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use INWX\Domrobot;

class Connector
{
    protected Domrobot $domrobot;

    public function __construct() {
        $this->domrobot = app()->make(Domrobot::class);

        $this->domrobot->setLanguage('de')->useJson();
        if(config('app.env') !== 'production') {
            $this->domrobot->useOte()->setDebug(true);
        }else {
            $this->domrobot->useLive();
        }

        if(config('internetworx.username') === null || config('internetworx.password') === null) {
            Log::warning('Please provide Internetworx Credentials');
        }else {
            $this->domrobot->login(
                config('internetworx.username'),
                config('internetworx.password')
            );
        }

        return $this;
    }

    public function isOte() {
        return $this->domrobot->isOte();
    }

    protected function processResponse($response, string $object): Collection {
        $code = Arr::get($response, 'code');
        if(!Arr::has($response, 'resData.' . $object) && !Str::is([1000, 1001], $code)) {
            try {
                Log::error(json_encode($response));
            }catch(BindingResolutionException $bindingResolutionException) {
                return collect();
            }
        }

        return collect(Arr::get($response, 'resData.'.$object));
    }
}
