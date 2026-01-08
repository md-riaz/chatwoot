<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Account;
use App\Models\Article;
use App\Models\Contact;
use App\Observers\AccountObserver;
use App\Observers\ArticleObserver;
use App\Observers\ContactObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Account::observe(AccountObserver::class);
        Article::observe(ArticleObserver::class);
        Contact::observe(ContactObserver::class);
        //
    }
}
