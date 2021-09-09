<?php

namespace App\Services\Forge\Endpoints;

use App\Services\Forge\Connector;

class CredentialsEndpoint extends Connector
{
    public function index() {
        return $this->getRequest('/credentials')->object();
    }
}
