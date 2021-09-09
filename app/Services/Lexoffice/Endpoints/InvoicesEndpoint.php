<?php

namespace App\Services\Lexoffice\Endpoints;

use App\Models\CustomerInvoice;
use App\Services\Lexoffice\Connector;

class InvoicesEndpoint extends Connector
{
    public function get(CustomerInvoice $customerInvoice) {
        $response = $this->getRequest('/invoices/' . $customerInvoice->lexoffice_id);

        if($response->ok()) {
            return $response->object();
        }
    }

    public function getRedirect(CustomerInvoice $customerInvoice) {
        return 'https://app.lexoffice.de/permalink/invoices/view/' . $customerInvoice->lexoffice_id;
    }
}
