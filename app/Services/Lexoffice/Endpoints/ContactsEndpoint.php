<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Services\Lexoffice\Connector;

class ContactsEndpoint extends Connector
{
    protected ?string $filterEmail = null;

    protected ?string $filterName = null;

    protected ?int $filterNumber = null;

    protected bool $filterCustomer = false;

    protected bool $filterVendor = false;

    public function filterEmail(string $email): static {
        $this->filterEmail = $email;

        return $this;
    }

    public function filterName(string $name): static {
        $this->filterName = $name;

        return $this;
    }

    public function filterNumber(int $number): static {
        $this->filterNumber = $number;

        return $this;
    }

    public function onlyCustomer(): static {
        $this->filterCustomer = true;

        return $this;
    }

    public function onlyVendor(): static {
        $this->filterVendor = true;

        return $this;
    }

    public function index() {
        $query = [];

        if($this->filterEmail) {
            $query['email'] = $this->filterEmail;
        }

        if($this->filterName) {
            $query['name'] = $this->filterName;
        }

        if($this->filterNumber) {
            $query['number'] = $this->filterNumber;
        }

        if($this->filterCustomer) {
            $query['customer'] = "true";
        }

        if($this->filterVendor) {
            $query['vendor'] = "true";
        }

        $response = $this->getRequest('/contacts', $query);

        if($response->ok()) {
            return $response->object();
        }
    }

    public function get(string $id) {
        $response = $this->getRequest('/contacts/' . $id);

        if($response->ok()) {
            return $response->object();
        }
    }
}
