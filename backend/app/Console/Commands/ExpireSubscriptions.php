<?php

namespace App\Console\Commands;

use App\Services\SubscriptionRoleService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire overdue subscriptions and demote organizers without active plan to pilot';

    public function handle(SubscriptionRoleService $roleService): int
    {
        $result = $roleService->expireDueSubscriptions();

        $this->info("Suscripciones expiradas: {$result['expired']}");
        $this->info("Usuarios pasados a piloto: {$result['demoted']}");

        return self::SUCCESS;
    }
}
