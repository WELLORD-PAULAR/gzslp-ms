<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaginationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = $this->demoUser('pagination-demo-customer@example.com', 'Pagination Demo Customer');

        foreach (range(1, 27) as $number) {
            $this->demoUser(
                sprintf('pagination-demo-%02d@example.com', $number),
                sprintf('Pagination Demo Customer %02d', $number),
            );
        }

        foreach (range(1, 26) as $number) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            Product::firstOrCreate(
                ['name' => 'Pagination Demo Product '.$suffix],
                ['price' => '10.00', 'is_active' => true],
            );
            Service::firstOrCreate(
                ['name' => 'Pagination Demo Service '.$suffix],
                ['starting_price' => '10.00', 'is_active' => true],
            );
            Supplier::firstOrCreate(
                ['name' => 'Pagination Demo Supplier '.$suffix],
                ['is_active' => true],
            );
            InventoryItem::firstOrCreate(
                ['sku' => 'PAGINATION-DEMO-'.$suffix],
                [
                    'name' => 'Pagination Demo Supply '.$suffix,
                    'category' => 'paper',
                    'unit' => 'sheet',
                    'quantity_on_hand' => '10.000',
                    'reorder_level' => '1.000',
                    'is_active' => true,
                ],
            );
        }

        if (! Order::where('customer_note', 'like', 'Pagination demo order %')->exists()) {
            foreach (range(1, 26) as $number) {
                $customer->orders()->create([
                    'status' => Order::STATUS_PENDING,
                    'subtotal' => '10.00',
                    'customer_note' => 'Pagination demo order '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                ]);
            }
        }

        $item = InventoryItem::where('sku', 'PAGINATION-DEMO-01')->firstOrFail();
        $recorder = User::where('role', User::ROLE_ADMIN)->firstOrFail();

        if (! InventoryMovement::where('notes', 'like', 'Pagination demo movement %')->exists()) {
            foreach (range(1, 26) as $number) {
                InventoryMovement::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => $recorder->id,
                    'type' => InventoryMovement::TYPE_RECEIVED,
                    'quantity_change' => '1.000',
                    'quantity_before' => '0.000',
                    'quantity_after' => '1.000',
                    'notes' => 'Pagination demo movement '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'created_at' => now()->subMinutes($number),
                ]);
            }
        }
    }

    private function demoUser(string $email, string $name): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = 'password123';
        $user->role = User::ROLE_CUSTOMER;
        $user->is_active = true;
        $user->email_verified_at ??= now();
        $user->save();

        return $user;
    }
}
