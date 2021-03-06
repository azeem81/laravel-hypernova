<?php

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Folklore\Image\Exception\FormatException;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [
            __DIR__.'/views/'
        ]);
    }

    protected function clearViewCache()
    {
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->call('view:clear');
    }

    protected function assertHtmlForJob($html, $job, $uuid = null)
    {
        $document = new \DOMDocument();
        $document->loadHTML($html);
        $div = $document->documentElement->getElementsByTagName('div')[0];
        $this->assertEquals($job['name'], $div->getAttribute('data-hypernova-key'));
        if ($uuid) {
            $this->assertEquals($uuid, $div->getAttribute('data-hypernova-id'));
        } else {
            $this->assertFalse(empty($div->getAttribute('data-hypernova-id')));
        }

        $script = $document->documentElement->getElementsByTagName('script')[0];
        $this->assertEquals($job['name'], $script->getAttribute('data-hypernova-key'));
        if ($uuid) {
            $this->assertEquals($uuid, $script->getAttribute('data-hypernova-id'));
        } else {
            $this->assertFalse(empty($script->getAttribute('data-hypernova-id')));
        }
        $json = trim(
            preg_replace(
                '/^\<\!\-\-/',
                '',
                preg_replace('/\-\-\>$/', '', $script->textContent)
            )
        );
        $data = json_decode($json, true);
        $this->assertEquals($job['data'], $data);
    }

    protected function getPackageProviders($app)
    {
        return [
            \Folklore\Hypernova\HypernovaServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Hypernova' => \Folklore\Hypernova\Support\Facades\Hypernova::class
        ];
    }
}
