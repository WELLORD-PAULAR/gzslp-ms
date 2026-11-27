<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Console\Command;

class ClearPaginationDemo extends Command
{
    protected $signature = 'app:clear-pagination-demo';

    protected $description = 'Remove the demo records created by PaginationDemoSeeder.';

    public function handle(): int
    {
        InventoryMovement::where('notes', 'like', 'Pagination demo movement %')->delete();

        $demoCustomerIds = User::where('email', 'like', 'pagination-demo%@example.com')->pluck('id');
        Order::whereIn('user_id', $demoCustomerIds)
            ->where('customer_note', 'like', 'Pagination demo order %')
            ->delete();

        $deleted = User::whereKey($demoCustomerIds)->delete();
        $deleted += Product::where('name', 'like', 'Pagination Demo Product %')->delete();
        $deleted += Service::where('name', 'like', 'Pagination Demo Service %')->delete();
        $deleted += Supplier::where('name', 'like', 'Pagination Demo Supplier %')->delete();

        $demoItemIds = InventoryItem::where('sku', 'like', 'PAGINATION-DEMO-%')->pluck('id');
        $deleted += InventoryItem::whereKey($demoItemIds)->delete();

        $this->info("Removed {$deleted} pagination demo records.");

        return self::SUCCESS;
    }
}
