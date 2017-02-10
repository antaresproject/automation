<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */



namespace Antares\Automation\Model\TestCase;

use Antares\Automation\Console\ScheduleRunCommand;
use Antares\Automation\Model\Jobs;
use Antares\Console\Schedule;
use Antares\Console\Scheduling\Event;
use Antares\Testing\TestCase;
use Antares\View\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery as m;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Process\Process;

class ScheduleRunCommandTest extends TestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests Antares\Automation\Console\ScheduleRunCommand::__construct
     */
    public function testConstruct()
    {
        $schedule                                              = m::mock(Schedule::class);
        $this->app[Jobs::class]                                = m::mock(Jobs::class);
        $this->app->config['antares/automation::memory.model'] = Jobs::class;
        $this->assertInstanceOf(ScheduleRunCommand::class, new ScheduleRunCommand($schedule));
    }

    /**
     * Tests Antares\Automation\Console\ScheduleRunCommand::fire
     */
    public function testFire()
    {
        $schedule                                              = m::mock(Schedule::class);
        $this->app[Jobs::class]                                = m::mock(Jobs::class);
        $this->app->config['antares/automation::memory.model'] = Jobs::class;
        $command                                               = new ScheduleRunCommand($schedule);
        $schedule->shouldReceive('dueEvents')->withAnyArgs()->andReturn([]);

        $command->setOutput($outputMock = m::mock(OutputStyle::class));
        $info       = "<info>No scheduled commands are ready to run.</info>";
        $outputMock->shouldReceive('writeln')->with($info, 32)->once()->andReturn($info);
        $this->assertNull($command->fire());
    }

    /**
     * Tests Antares\Automation\Console\SyncCommand::setOutput
     */
    public function testSetOutput()
    {
        $schedule                                              = m::mock(Schedule::class);
        $this->app[Jobs::class]                                = m::mock(Jobs::class);
        $this->app->config['antares/automation::memory.model'] = Jobs::class;
        $command                                               = new ScheduleRunCommand($schedule);
        $this->assertInstanceOf(ScheduleRunCommand::class, $command->setOutput($outputMock                                            = m::mock(OutputStyle::class)));
    }

    /**
     * Tests Antares\Automation\Console\ScheduleRunCommand::fire
     */
    public function testFireWithEvents()
    {
        $schedule = m::mock(Schedule::class);
        $jobs     = m::mock(Jobs::class);
        $jobs->shouldReceive('getTable')->withNoArgs()->andReturn('tbl_jobs')
                ->shouldReceive('getConnectionName')->withAnyArgs()->andReturn('mysql')
                ->shouldReceive('hydrate')->withAnyArgs()->andReturn(new Collection([1 => 2]))
                ->shouldReceive('where')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('first')->withNoArgs()->andReturnSelf()
                ->shouldReceive('delete')->withNoArgs()->andReturnSelf()
                ->shouldReceive('newCollection')->withAnyArgs()->andReturn(new Collection());

        $this->app[Jobs::class]                                = $jobs;
        $this->app->config['antares/automation::memory.model'] = Jobs::class;
        $command                                               = new ScheduleRunCommand($schedule);


        $event   = m::mock(Event::class);
        $event->shouldReceive('filtersPass')->withAnyArgs()->andReturn(true)
                ->shouldReceive('getSummaryForDisplay')->withAnyArgs()->andReturn('foo')
                ->shouldReceive('run')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('getProcess')->withAnyArgs()->andReturn($process = m::mock(Process::class));


        $process->shouldReceive('isSuccessful')->andReturn(false)
                ->shouldReceive('stop')->andReturn(false);


        $event->command   = $commandEventMock = m::mock(Command::class);
        $commandEventMock->shouldReceive('setOutput')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('getSummaryForDisplay')->withAnyArgs()->andReturn('foo');

        $schedule->shouldReceive('rejectCommand')->withAnyArgs()->andReturn('foo:start');
        $schedule->shouldReceive('dueEvents')->withAnyArgs()->andReturn([$event]);



        $command->setOutput($outputMock = m::mock(OutputStyle::class));
        $info       = "<info>No scheduled commands are ready to run.</info>";
        $outputMock->shouldReceive('writeln')->withAnyArgs()->once()->andReturn($info);
        $foundation = m::mock('\Illuminate\Contracts\Container\Container');

        DB::shouldReceive('transaction')
                ->once()
                ->with(m::type('Closure'))
                ->andReturn(m::mock('Illuminate\Database\Query\Builder'))
                ->shouldReceive('beginTransaction')
                ->once()
                ->withNoArgs()
                ->andReturnNull()
                ->shouldReceive('rollback')
                ->once()
                ->withNoArgs()
                ->andReturnNull();



        $command->setLaravel($foundation);
        $this->assertNull($command->fire());
    }

}
