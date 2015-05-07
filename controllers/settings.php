<?php

/**
 * Events controller.
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

class Settings extends ClearOS_Controller
{
    /**
     * Settings default controller.
     *
     * @return view
     */

    function index()
    {
        $this->view();
    }

    /**
     * Edit view.
     *
     * @return view
     */

    function edit()
    {
        $this->_item('edit');
    }

    /**
     * View view.
     *
     * @return view
     */

    function view()
    {
        $this->_item('view');
    }

    /**
     * Common view/edit view.
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _item($form_type)
    {
        // Load libraries
        //---------------

        $this->load->library('events/Events');
		$this->lang->load('events');
		$this->lang->load('base');

        $this->form_validation->set_policy('status', 'events/Events', 'validate_status', FALSE);
        if ($this->input->post('status')) {
            $this->form_validation->set_policy('autopurge', 'events/Events', 'validate_autopurge', TRUE);
            $this->form_validation->set_policy('instant_status', 'events/Events', 'validate_instant_status', FALSE);
            $this->form_validation->set_policy('daiyl_status', 'events/Events', 'validate_daily_status', FALSE);
            if ($this->input->post('instant_status')) {
                $this->form_validation->set_policy('instant_warning', 'events/Events', 'validate_flags', FALSE);
                $this->form_validation->set_policy('instant_critical', 'events/Events', 'validate_flags', FALSE);
                $this->form_validation->set_policy('instant_email', 'events/Events', 'validate_email', TRUE);
            }
            if ($this->input->post('daily_status')) {
                $this->form_validation->set_policy('daily_info', 'events/Events', 'validate_flags', FALSE);
                $this->form_validation->set_policy('daily_warning', 'events/Events', 'validate_flags', FALSE);
                $this->form_validation->set_policy('daily_critical', 'events/Events', 'validate_flags', FALSE);
                $this->form_validation->set_policy('daily_email', 'events/Events', 'validate_email', TRUE);
            }
        }

        $form_ok = $this->form_validation->run();

        // Extra validation
        //-----------------

        if ($form_ok) {
            if ($this->input->post('instant_status') && !$this->input->post('instant_warning') && !$this->input->post('instant_critical')) {
                $this->form_validation->set_error('instant_status', lang('events_instant_flags'));
                $form_ok = FALSE;
            }
            if ($this->input->post('daily_status') &&
                !$this->input->post('daily_info') &&
                !$this->input->post('daily_warning') &&
                !$this->input->post('daily_critical')) 
            {
                $this->form_validation->set_error('daily_status', lang('events_daily_flags'));
                $form_ok = FALSE;
            }
        }

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && ($form_ok === TRUE)) {

            try {
                $this->events->set_status($this->input->post('status'));
                $this->events->set_instant_status($this->input->post('instant_status'));
                $this->events->set_daily_status($this->input->post('daily_status'));
                if ($this->input->post('status')) {
                    $this->events->set_autopurge($this->input->post('autopurge'));
                    if ($this->input->post('instant_status')) {
                        $this->events->set_instant_flags(
                            FALSE,
                            (bool)$this->input->post('instant_warning'),
                            (bool)$this->input->post('instant_critical')
                        );
                        $this->events->set_instant_email($this->input->post('instant_email'));
                    }
                    if ($this->input->post('daily_status')) {
                        $this->events->set_daily_flags(
                            (bool)$this->input->post('daily_info'),
                            (bool)$this->input->post('daily_warning'),
                            (bool)$this->input->post('daily_critical')
                        );
                        $this->events->set_daily_email($this->input->post('daily_email'));
                    }
                }
                $this->page->set_message(lang('base_configuration_updated'), 'info');
                redirect('/events');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        $data['status'] = $this->events->get_status();
        $data['autopurge'] = $this->events->get_autopurge();
        $data['autopurge_options'] = $this->events->get_autopurge_options();
        $data['instant_status'] = $this->events->get_instant_status();
        $data['instant_email'] = $this->events->get_instant_email();
        $data['daily_status'] = $this->events->get_daily_status();
        $data['daily_email'] = $this->events->get_daily_email();
        list($data['instant_info'], $data['instant_warning'], $data['instant_critical']) = $this->events->get_instant_flags();
        list($data['daily_info'], $data['daily_warning'], $data['daily_critical']) = $this->events->get_daily_flags();
        
        $daily_flags = $this->events->get_instant_flags();
        $data['flags'] = 7;  // Default all severity levels (1, 2 and 4 bits)
        if ($this->session->userdata('events_flags') !== FALSE)
            $data['flags'] = $this->session->userdata('events_flags');
        $this->page->view_form('events/settings', $data, lang('base_settings'));
	}
}
