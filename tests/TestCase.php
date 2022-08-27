<?php

namespace  Yobidoyobi\Form\Tests;

use Yobidoyobi\Form\SymfonyFormProvider;
use Orchestra\Testbench\TestCase as TestBenchTestCase;

abstract class TestCase extends TestBenchTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SymfonyFormProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->make('Illuminate\Contracts\Http\Kernel')
            ->pushMiddleware('Illuminate\Session\Middleware\StartSession');

        $app['config']->set('app.debug', true);
        $app['view']->addNamespace('forms', __DIR__ .'/views');
    }
}
