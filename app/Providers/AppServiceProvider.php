<?php

namespace App\Providers;

use App\Domain\Transfer\Contracts\Repositories\TransferEventRepositoryInterface;
use App\Infrastructure\Transfer\SqliteTransferEventRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TransferEventRepositoryInterface::class,
            SqliteTransferEventRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
