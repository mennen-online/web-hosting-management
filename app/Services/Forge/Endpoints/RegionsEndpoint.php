<?php

namespace App\Services\Forge\Endpoints;

use App\Services\Forge\Connector;

class RegionsEndpoint extends Connector
{
    public function index()
    {
        return $this->getRequest('/regions')->object();
    }
}
