<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Services\Lexoffice\Connector;

class FilesEndpoint extends Connector
{
    public function get(string $documentFileId)
    {
        return $this->getRequest('/files/' . $documentFileId);
    }
}
