<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Task;
use App\Models\TaskTopic;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoicePosition;
use App\Models\CustomerProduct;
use App\Models\Domain;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TaskTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $role = Role::byName('Administrator');

        $this->user->roles()->attach($role);

        TaskTopic::factory()->count(10)->create();
    }

    public function testCreateTasksForCustomer() {
        $user = User::factory()->has(Customer::factory())->create();

        $task = Task::factory()
            ->for($this->user)
            ->create([
            'taskable_type' => Customer::class,
            'taskable_id' => $user->customer->id
        ]);

        $this->assertModelExists($task);

        $this->assertModelExists($task->task_topic);
    }

    public function testCreateTasksForCustomerWithToDoByDate() {
        $user = User::factory()->has(Customer::factory())->create();

        $task = Task::factory()
            ->for($this->user)
            ->withToDoBy()->create([
            'taskable_type' => Customer::class,
            'taskable_id' => $user->customer->id
        ]);

        $this->assertModelExists($task);

        $this->assertModelExists($task->task_topic);

        $date = Carbon::parse($task->to_do_by);

        $this->assertTrue($date->isValid());

        $this->assertTrue($date->isFuture());
    }
}
