<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\DomainProduct;
use App\Models\User;
use App\Services\Internetworx\Objects\ContactObject;
use App\Services\Internetworx\Objects\DomainObject;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class SyncInternetworxDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'internetworx:domains:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Domains and their Handles with Internetworx';

    protected DomainObject $domainObject;

    protected ContactObject $contactObject;

    protected Collection $domainProductCollection;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        config()->set('app.env', 'production');

        $this->domainProductCollection = DomainProduct::all();

        $this->domainObject = app()->make(DomainObject::class);

        $this->contactObject = app()->make(ContactObject::class);

        $this->withProgressBar(
            $this->domainObject->index(0, 5000),
            function ($domain) {
                $domainProduct = $this->domainProductCollection->filter(function ($domainProduct) use ($domain) {
                    if (Str::contains($domain['domain'], '.'.$domainProduct->tld)) {
                        return $domainProduct;
                    }
                })->first();

                $newDomain = Domain::firstOrCreate(
                    [
                        'name' => $domain['domain']
                    ],
                    [
                        'domain_product_id' => $domainProduct->id,
                        'registrar_id' => $domain['roId']
                    ]
                );
                $contactId = $domain['registrant'];

                $contact = $this->contactObject->index(1, 1, $contactId)[0];

                $user = User::where('email', $contact['email'])->first();

                if ($user === null) {
                    $newDomain->delete();
                } else {
                    $customerProduct = $user->customerProducts()->updateOrCreate([
                        'customer_id' => $user->customer->id,
                        'domain_id'   => $newDomain->id,
                    ], [
                        'customer_id' => $user->customer->id,
                        'domain_id'   => $newDomain->id,
                        'active'      => true
                    ]);

                    if ($customerProduct->wasRecentlyCreated) {
                        $customerProduct->update([
                            'product_id'  => null,
                            'server_id'   => null,
                        ]);
                    }
                }
            }
        );

        return 0;
    }
}
