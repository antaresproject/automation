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
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Automation\Http\Handlers;

use Antares\Contracts\Authorization\Authorization;
use Antares\Foundation\Support\MenuHandler;
use Antares\Automation\Model\Jobs;

class AutomationLogsShowBreadcrumbMenu extends MenuHandler
{

    /**
     * Menu configuration.
     *
     * @var array
     */
    protected $menu = [
        'id'   => 'automation-breadcrumb',
        'link' => 'antares::automation/logs/index',
        'icon' => null,
        'boot' => [
            'group' => 'menu.top.automation-show',
            'on'    => 'antares/automation::admin.index.show'
        ]
    ];

    /**
     * Entity instance
     *
     * @var Jobs 
     */
    protected $model = null;

    /**
     * Get the title.
     * @param  string  $value
     * @return string
     */
    public function getTitleAttribute($value)
    {
        return $this->model->name;
    }

    /**
     * Check authorization to display the menu.
     * @param  \Antares\Contracts\Authorization\Authorization  $acl
     * @return bool
     */
    public function authorize(Authorization $acl)
    {
        return true;
    }

    /**
     * Create a handler.
     * @return void
     */
    public function handle()
    {
        if (!$this->passesAuthorization() or is_null($id = from_route('id'))) {
            return;
        }
        $this->model = Jobs::query()->findOrFail(from_route('id'));

        $this->createMenu();
        $this->handler
                ->add('automation-run', '^:automation-breadcrumb')
                ->title(trans('Run'))
                ->icon('zmdi-play-circle-outline')
                ->link(handles('antares::automation/run/' . $id))
                ->attributes([
                    'class'            => "triggerable confirm",
                    'data-title'       => trans('antares/automation::messages.ask'),
                    'data-description' => trans('antares/automation::messages.running_job_message', ['name' => $this->model->value['title']])
        ]);

        $this->handler
                ->add('automation-edit', '^:automation-breadcrumb')
                ->title(trans('antares/automation::messages.edit'))
                ->icon('zmdi-edit')
                ->link(handles("antares::automation/edit/" . $id));
    }

}
