<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\CustomerInvoice;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    private array $models = [
        User::class,
        Customer::class,
        Product::class,
        \App\Models\Server::class,
        Domain::class,
        CustomerProduct::class,
        CustomerInvoice::class,
        CustomerContact::class,
        Role::class,
        Permission::class
    ];

    private array $actions = [
        'viewAny',
        'view',
        'store',
        'update',
        'delete',
        'forceDelete'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->models as $modelFQCN) {
            foreach($this->actions as $action) {
                $model = new $modelFQCN();
                $permissionName = $action . '-' . $modelFQCN;
                if($action === 'forceDelete' && method_exists($model, 'forceDelete') || $action !== 'forceDelete') {
                    Permission::create(
                        [
                            'name' => $permissionName
                        ]
                    );
                }
            }
        }
    }
}
