<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Relation::morphMap([
            'Channel::Api' => \App\Models\Channels\Api::class,
            'Channel::Email' => \App\Models\Channels\Email::class,
            'Channel::FacebookPage' => \App\Models\Channels\FacebookPage::class,
            'Channel::Instagram' => \App\Models\Channels\Instagram::class,
            'Channel::Line' => \App\Models\Channels\Line::class,
            'Channel::Sms' => \App\Models\Channels\Sms::class,
            'Channel::Telegram' => \App\Models\Channels\Telegram::class,
            'Channel::TikTok' => \App\Models\Channels\TikTok::class,
            'Channel::TwilioSms' => \App\Models\Channels\TwilioSms::class,
            'Channel::TwitterProfile' => \App\Models\Channels\TwitterProfile::class,
            'Channel::Voice' => \App\Models\Channels\Voice::class,
            'Channel::WebWidget' => \App\Models\Channels\WebWidget::class,
            'Channel::Whatsapp' => \App\Models\Channels\Whatsapp::class,
        ]);

        Account::observe(AccountObserver::class);
        Article::observe(ArticleObserver::class);
        Contact::observe(ContactObserver::class);
    }
}
