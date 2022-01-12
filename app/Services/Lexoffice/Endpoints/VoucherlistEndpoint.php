<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Services\Lexoffice\Connector;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class VoucherlistEndpoint extends Connector
{
    protected ?string $voucherType = null;

    protected ?string $voucherStatus = null;

    protected ?bool $archived = null;

    protected ?string $contactId = null;

    protected ?Carbon $voucherDateFrom = null;

    protected ?Carbon $voucherDateTo = null;

    protected ?Carbon $createdDateFrom = null;

    protected ?Carbon $createdDateTo = null;

    protected ?Carbon $updatedDateFrom = null;

    protected ?Carbon $updatedDateTo = null;

    /**
     * @param  string  $voucherType
     */
    public function setVoucherType(string $voucherType): void {
        $this->voucherType = $voucherType;
    }

    /**
     * @param  string  $voucherStatus
     */
    public function setVoucherStatus(string $voucherStatus): void {
        $this->voucherStatus = $voucherStatus;
    }

    /**
     * @param  bool  $archived
     */
    public function setArchived(bool $archived): void {
        $this->archived = $archived;
    }

    /**
     * @param  string  $contactId
     */
    public function setContactId(string $contactId): void {
        $this->contactId = $contactId;
    }

    /**
     * @param  string  $voucherDateFrom
     */
    public function setVoucherDateFrom(string $voucherDateFrom): void {
        $this->voucherDateFrom = Carbon::parse($voucherDateFrom);
    }

    /**
     * @param  string  $voucherDateTo
     */
    public function setVoucherDateTo(string $voucherDateTo): void {
        $this->voucherDateTo = Carbon::parse($voucherDateTo);
    }

    /**
     * @param  string  $createdDateFrom
     */
    public function setCreatedDateFrom(string $createdDateFrom): void {
        $this->createdDateFrom = Carbon::parse($createdDateFrom);
    }

    /**
     * @param  string  $createdDateTo
     */
    public function setCreatedDateTo(string $createdDateTo): void {
        $this->createdDateTo = Carbon::parse($createdDateTo);
    }

    /**
     * @param  string  $updatedDateFrom
     */
    public function setUpdatedDateFrom(string $updatedDateFrom): void {
        $this->updatedDateFrom = Carbon::parse($updatedDateFrom);
    }

    /**
     * @param  string  $updatedDateTo
     */
    public function setUpdatedDateTo(string $updatedDateTo): void {
        $this->updatedDateTo = Carbon::parse($updatedDateTo);
    }

    public function index() {
        $query = [];

        if ($this->voucherType) {
            $query['voucherType'] = $this->voucherType;
        }

        if ($this->voucherStatus) {
            $query['voucherStatus'] = $this->voucherStatus;
        }

        if ($this->archived !== null) {
            $query['archived'] = $this->archived;
        }

        if ($this->contactId !== null) {
            $query['contactId'] = $this->contactId;
        }

        if ($this->voucherDateFrom !== null && $this->voucherDateFrom->isValid()) {
            $query['voucherDateFrom'] = $this->voucherDateFrom->format('Y-m-d');
        }

        if ($this->voucherDateTo !== null && $this->voucherDateTo->isValid()) {
            $query['voucherDateTo'] = $this->voucherDateTo->format('Y-m-d');
        }

        if ($this->createdDateFrom !== null && $this->createdDateFrom->isValid()) {
            $query['createdDateFrom'] = $this->createdDateFrom->format('Y-m-d');
        }

        if ($this->createdDateTo !== null && $this->createdDateTo->isValid()) {
            $query['createdDateTo'] = $this->createdDateTo->format('Y-m-d');
        }

        if ($this->updatedDateFrom !== null && $this->updatedDateFrom->isValid()) {
            $query['updatedDateFrom'] = $this->updatedDateFrom->format('Y-m-d');
        }

        if ($this->updatedDateTo !== null && $this->updatedDateTo->isValid()) {
            $query['updatedDateTo'] = $this->updatedDateTo->format('Y-m-d');
        }

        return $this->getRequest('/voucherlist?', $query);
    }
}
