<?php

/**
 * Events manager setting view.
 *
 * @category   apps
 * @package    events
 * @subpackage views
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

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('base');
$this->lang->load('events');

///////////////////////////////////////////////////////////////////////////////
// Form open
///////////////////////////////////////////////////////////////////////////////

echo form_open('events/settings');
echo form_header(lang('base_settings'));

///////////////////////////////////////////////////////////////////////////////
// Form fields and buttons
///////////////////////////////////////////////////////////////////////////////

$read_only = FALSE;
$buttons = array(
    form_submit_update('submit'),
    anchor_cancel('/app/events')
);

echo fieldset_header(lang('events_general_settings'));

echo field_toggle_enable_disable('status', $status, lang('events_monitoring_status'), $read_only);
echo field_dropdown('autopurge', $autopurge_options, $autopurge, lang('events_autopurge'), $read_only);

echo fieldset_header(lang('events_instant_notifications'));
echo field_toggle_enable_disable('alert_notifications', $alert_notifications, lang('base_status'), $read_only);
echo field_checkbox('instant_warning', $instant_warning, lang('events_warning'), $read_only);
echo field_checkbox('instant_critical', $instant_critical, lang('events_critical'), $read_only);
echo field_textarea('instant_email', implode("\n", $instant_email), lang('events_email'), $read_only);

echo fieldset_header(lang('events_daily_event_summary'));
echo field_toggle_enable_disable('daily_notifications', $daily_notifications, lang('base_status'), $read_only);
echo field_checkbox('daily_info', $daily_info, lang('events_info'), $read_only);
echo field_checkbox('daily_warning', $daily_warning, lang('events_warning'), $read_only);
echo field_checkbox('daily_critical', $daily_critical, lang('events_critical'), $read_only);
echo field_textarea('instant_email', implode("\n", $instant_email), lang('events_email'), $read_only);
echo field_button_set($buttons);

///////////////////////////////////////////////////////////////////////////////
// Form close
///////////////////////////////////////////////////////////////////////////////

echo form_footer();
echo form_close();

// Used for flag selectors in JS
echo "<input type='hidden' id='flags' class='theme-hidden' value='$flags' />";
