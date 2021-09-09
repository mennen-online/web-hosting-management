<?php

namespace App\Services\Internetworx\Objects;

use App\Services\Internetworx\Connector;

class ContactObject extends Connector
{
    public function index(int $page = 1, int $pageLimit = 250, ?int $id = null) {
        $params = [
            'page' => $page,
            'pagelimit' => $pageLimit
        ];

        if($id) {
            $params['id'] = $id;
        }

        return $this->domrobot->call('contact', 'list', $params);
    }
}
