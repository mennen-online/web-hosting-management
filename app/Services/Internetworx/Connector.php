<?php

namespace App\Services\Internetworx;

use Illuminate\Support\Facades\Log;
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
}
