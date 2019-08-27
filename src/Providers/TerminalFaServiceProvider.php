<?php

namespace TerminalFaApi\Providers;

use Illuminate\Support\ServiceProvider;

class TerminalFaServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot()
    {
        $this->registerConfig();
    }

    /**
     * Register config.
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/terminalfa.php' => config_path('terminalfa.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../../config/terminalfa.php', 'terminalfa'
        );
    }
}
