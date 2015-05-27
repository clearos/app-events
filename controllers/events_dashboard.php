<?php

/**
 * Events Dashboard controller.
 *
 * @category   apps
 * @package    events
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/events/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

class Events_Dashboard extends ClearOS_Controller
{
    /**
     * Dashboard default controller.
     *
     * @return view
     */

    function index()
    {
        // Default to last 24
        $this->last_24();
    }

    /**
     * Last 24 hour summary.
     *
     * @return view
     */

    function last_24()
    {
        // Load libraries
        //---------------

        $this->load->library('events/Events');
		$this->lang->load('events');
		$this->lang->load('base');

        $data['summary'] = $this->events->get_last_24_hour_summary();
        $this->page->view_form('events/dashboard/last_24', $data, lang('events_last_24_hours'));
	}
}
