<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Automation
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Automation\Processor\TestCase;

use Antares\Automation\Http\Presenters\IndexPresenter;
use Antares\Automation\Processor\IndexProcessor;
use Antares\Automation\Contracts\IndexListener;
use Antares\Testing\ApplicationTestCase;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Mockery as m;

class IndexProcessorTest extends ApplicationTestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
        $this->app['view']->addNamespace('antares/automation', realpath(getcwd() . '/resources/views'));
    }

    /**
     * create presenter stub instance
     * 
     * @return IndexPresenter
     */
    protected function getPresenter()
    {

        $breadcrumb = m::mock(\Antares\Automation\Http\Breadcrumb\Breadcrumb::class);
        $breadcrumb->shouldReceive('onList')->withNoArgs()->andReturn(null)
                ->shouldReceive('onShow')->withAnyArgs()->andReturn(null)
                ->shouldReceive('onEdit')->withAnyArgs()->andReturn(null);

        $automationDatatables        = $this->app->make(\Antares\Automation\Http\Datatables\Automation::class);
        $automationDetailsDatatables = $this->app->make(\Antares\Automation\Http\Datatables\AutomationDetails::class);
        return new IndexPresenter($this->app, $breadcrumb, $automationDatatables, $automationDetailsDatatables);
    }

    /**
     * gets stub instance
     * 
     * @return IndexProcessor
     */
    protected function getStub()
    {
        $kernel = m::mock(Kernel::class);
        return new IndexProcessor($this->getPresenter(), $kernel);
    }

    /**
     * Tests Antares\Automation\Processor\IndexProcessor::index
     */
    public function testIndex()
    {
        $stub = $this->getStub();
        $this->assertInstanceOf(View::class, $stub->index($this->app['request']));
    }

    /**
     * Tests Antares\Automation\Processor\IndexProcessor::show
     */
    public function testShow()
    {
        $stub          = $this->getStub();
        $indexListener = m::mock(IndexListener::class);
        $this->assertInstanceOf(View::class, $stub->show(1, $indexListener));
    }

    /**
     * Tests Antares\Automation\Processor\IndexProcessor::edit
     */
    public function testEdit()
    {
        $stub          = $this->getStub();
        $indexListener = m::mock(IndexListener::class);
        $indexListener->shouldReceive('showFailed')->andReturn(new RedirectResponse('#'));
        $this->assertInstanceOf(View::class, $stub->edit(1, $indexListener));
    }

    /**
     * Tests Antares\Automation\Processor\IndexProcessor::update
     * 
     * @expectedException  \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testUpdate()
    {
        $stub          = $this->getStub();
        $indexListener = m::mock(IndexListener::class);
        $indexListener->shouldReceive('updateSuccess')->andReturn(new RedirectResponse('#'));
        $this->assertInstanceOf(RedirectResponse::class, $stub->update($indexListener));
    }

}
