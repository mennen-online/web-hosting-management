<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Connector;
use App\Services\Lexoffice\Lexoffice;

class DunningEndpoint extends Connector
{
    public function get(string $id)
    {
        return $this->getRequest('/dunnings/'.$id);
    }

    public function create(CustomerInvoice $customerInvoice)
    {
        $invoiceData = app()->make(InvoicesEndpoint::class)->get($customerInvoice);

        $invoiceData->voucherDate = Lexoffice::buildLexofficeDate(now());

        return $this->postRequest('/dunnings?precedingSalesVoucherId=' . $invoiceData->id, (array)$invoiceData);
    }
}
