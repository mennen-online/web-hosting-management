<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\User;
use App\Models\Role;
use App\Services\Lexoffice\Endpoints\ContactsEndpoint;
use Illuminate\Support\Arr;
use ErrorException;
use Illuminate\Console\Command;
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
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $this->contactsEndpoint = app()->make(ContactsEndpoint::class);
        $this->customerRole = Role::byName('Customer');
        $customers = collect();
        if ($this->contactsEndpoint->isLexofficeAvailable()) {
            $page = 0;
            $results = $this->contactsEndpoint->setPageSize(500)->setPage($page)->index();
            if (property_exists($results, 'last')) {
                do {
                    $this->line("Processing Page ".$page, 'info');
                    $page += 1;
                    if ($page > 0) {
                        collect($results->content)->filter(function($contact) {
                            if(property_exists($contact->roles, 'customer')) {
                                return $contact;
                            }
                        })->each(function($contact) use($customers) {
                            $customers->add($contact);
                        });
                    }
                } while ($results->last === false);
            } else {
                $results->filter(function($contact) {
                    if(property_exists($contact->roles, 'customer')) {
                        return $contact;
                    }
                })->each(function($contact) use($customers) {
                    $customers->add($contact);
                });
            }
            $this->withProgressBar($customers, function($contact) {
                $this->processContact($contact);
            });
            return 0;
        }
        $this->error('Lexoffice is not active');
        return 0;
    }

    private function processContact($contact) {
        if (property_exists($contact, 'company') && property_exists($contact->company, 'contactPersons')) {
            $contactPerson = collect($contact->company->contactPersons)->filter(function ($contactPerson) {
                if ($contactPerson->primary) {
                    return $contactPerson;
                }
            })->first();

            if ($contactPerson) {
                $user = User::firstOrCreate(
                    [
                        'email' => $contactPerson->emailAddress,
                    ],
                    [
                        'email'      => $contactPerson->emailAddress,
                        'first_name' => $contactPerson->firstName,
                        'last_name'  => $contactPerson->lastName,
                        'password'   => Hash::make($contactPerson->emailAddress)
                    ]);

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
                $contactPerson = collect($contact->company->contactPersons)->filter(function ($contactPerson) {
                    if ($contactPerson->primary) {
                        return $contactPerson;
                    }
                })->first();

                if ($contactPerson) {
                    collect($contact->company->contactPersons)->each(function ($contactPerson) use ($customer) {
                        $customer->contacts()->create([
                            'salutation' => $contactPerson->salutation,
                            'first_name' => $contactPerson->firstName,
                            'last_name'  => $contactPerson->lastName,
                            'email'      => $contactPerson->emailAddress,
                            'phone'      => $contactPerson->phoneNumber ?? "",
                            'primary'    => $contactPerson->primary
                        ]);
                    });
                }
            }
        }
        if (property_exists($contact, 'person') && property_exists($contact, 'emailAddresses') && property_exists($contact, 'phoneNumbers')) {
            try {
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
            } catch (ErrorException $errorException) {
                dd($contact);
            }
            $user = User::firstOrCreate([
                'email' => $email
            ], [
                'first_name' => $contact->person->firstName,
                'last_name'  => $contact->person->lastName,
                'email'      => $email,
                'password'   => Hash::make($email)
            ]);

            $customer = $user->customer()->firstOrCreate([
                'lexoffice_id' => $contact->id,
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
                    'last_name'  => $contact->person->lastName,
                    'email'      => $email,
                    'phone'      => $phone ?? ""
                ]);
        }
    }
}
