<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class SyncLexofficeContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lexoffice:contacts:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and Update lokal Contacts with Lexoffice';

    protected ContactsEndpoint $contactsEndpoint;

    protected Role $customerRole;

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
        $this->contactsEndpoint = app()->make(ContactsEndpoint::class);
        /**
         * @phpstan-ignore-next-line
         */
        $this->customerRole = Role::byName('Customer');
        $customers = collect();
        if ($this->contactsEndpoint->isLexofficeAvailable()) {
            $page = 0;
            do {
                $results = $this->contactsEndpoint->setPageSize(500)->setPage($page)->onlyCustomer()->index();
                $this->line("Processing Page " . $page, 'info');
                $page += 1;
                collect($results->content)->each(function ($contact) use ($customers) {
                    $customers->add($contact);
                });
            } while ($results->last === false);

            $this->withProgressBar($customers, function ($contact) {
                $this->processContact($contact);
            });
            return 0;
        }
        $this->error('Lexoffice is not active');
        return 0;
    }

    private function processContact($contact)
    {
        if (property_exists($contact, 'company') && property_exists($contact->company, 'contactPersons')) {
            $contactPerson = collect($contact->company->contactPersons)->filter(
                function ($contactPerson) {
                    if ($contactPerson->primary) {
                        return $contactPerson;
                    }
                }
            )->first();

            if ($contactPerson) {
                $user = User::firstOrCreate(
                    [
                        'email' => $contactPerson->emailAddress,
                    ],
                    [
                        'email' => $contactPerson->emailAddress,
                        'first_name' => $contactPerson->firstName,
                        'last_name' => $contactPerson->lastName,
                        'password' => Hash::make($contactPerson->emailAddress)
                    ]
                );

                $user->roles()->using($this->customerRole);

                $customer = $user->customer()->firstOrCreate(
                    [
                        'lexoffice_id' => $contact->id,
                        'number'       => $contact?->roles?->customer?->number ?? "",
                        'company'      => json_encode($contact->company),
                        'salutation'   => $contactPerson->salutation,
                        'email'        => $contactPerson->emailAddress,
                        'first_name'   => $contactPerson->firstName,
                        'last_name'    => $contactPerson->lastName,
                    ]
                );
            }

            if (property_exists($contact->company, 'contactPersons')) {
                $contactPerson = collect($contact->company->contactPersons)->filter(
                    function ($contactPerson) {
                        if ($contactPerson->primary) {
                            return $contactPerson;
                        }
                    }
                )->first();

                if ($contactPerson && isset($contact) && isset($customer)) {
                    collect($contact->company->contactPersons)->each(
                        function ($contactPerson) use ($customer) {
                            $customer->contacts()->create(
                                [
                                    'salutation' => $contactPerson->salutation,
                                    'first_name' => $contactPerson->firstName,
                                    'last_name' => $contactPerson->lastName,
                                    'email' => $contactPerson->emailAddress,
                                    'phone' => $contactPerson->phoneNumber ?? "",
                                    'primary' => $contactPerson->primary
                                ]
                            );
                        }
                    );
                }
            }
        }
        if (property_exists($contact, 'person')
            && property_exists($contact, 'emailAddresses')
            && property_exists($contact, 'phoneNumbers')) {
            if (property_exists($contact->emailAddresses, 'business')) {
                $email = $contact->emailAddresses->business[0];
            } elseif (property_exists($contact->emailAddresses, 'private')) {
                $email = $contact->emailAddresses->private[0];
            }

            if (property_exists($contact->phoneNumbers, 'business')) {
                $phone = $contact->phoneNumbers->business[0];
            } elseif (property_exists($contact->phoneNumbers, 'private')) {
                $phone = $contact->phoneNumbers->private[0];
            }

            if (isset($email)) {
                $user = User::firstOrCreate(
                    [
                        'email' => $email
                    ],
                    [
                        'first_name' => $contact->person->firstName,
                        'last_name' => $contact->person->lastName,
                        'email' => $email,
                        'password' => Hash::make($email)
                    ]
                );

                $customer = $user->customer()->firstOrCreate([
                        'lexoffice_id' => $contact->id,
                        'number'       => $contact->roles->customer->number,
                        'salutation'   => $contact->person->salutation,
                        'first_name'   => $contact->person->firstName,
                        'last_name'    => $contact->person->lastName,
                        'email'        => $contact->person->email ?? "",
                        'phone'        => $contact->person->phone ?? ""
                    ]);

                $customer->contacts()->firstOrCreate(
                    [
                        'email' => $email,
                    ],
                    [
                        'salutation' => $contact->person->salutation,
                        'first_name' => $contact->person->firstName,
                        'last_name' => $contact->person->lastName,
                        'email' => $email,
                        'phone' => $phone ?? ""
                    ]
                );
            }
        }

        if (isset($customer)
            && property_exists($contact, 'addresses')
            && property_exists($contact->addresses, 'billing')
            && Arr::has($contact->addresses->billing, "0")) {
            $address = $contact->addresses->billing[0];
            $customer->address()->create([
                    'type'         => 'billing',
                    'street'       => $address->street,
                    'supplement'   => $address->supplement ?? '',
                    'zip'          => $address->zip ?? '',
                    'city'         => $address->city ?? '',
                    'country_code' => $address->countryCode ?? ''
                ]);
        }
    }
}
