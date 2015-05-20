<?php

/**
 * Events manager view.
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

$this->lang->load('events');
$this->lang->load('base');

///////////////////////////////////////////////////////////////////////////////
// Anchors
///////////////////////////////////////////////////////////////////////////////

$anchors = anchor_custom('#', lang('events_acknowledge_all'), 'high', array('class' => 'events-acknowledge'));

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    '',
    lang('base_description'),
    lang('base_timestamp')
);

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

$options = array(
    'id' => 'events_list',
    'sort-default-col' => 2,
    'sort-default-dir' => 'desc',
    'ajax' => '/app/events/get_info',
    'no_action' => TRUE
);

echo summary_table(
    lang('events_events'),
    $anchors,
    $headers,
    NULL,
    $options
);

echo modal_confirm(
    lang('base_confirmation_required'),
    lang('events_confirm_delete'),
    "/app/events/delete/" . $events_delete_key,
    NULL,
    NULL,
    "events-modal-delete"
);
echo modal_confirm(
    lang('base_confirmation_required'),
    lang('events_acknowledge_all_info'),
    "/app/events/acknowledge",
    NULL,
    NULL,
    "events-modal-acknowledge"
);
// Used for flag selectors in JS
echo "<input type='hidden' id='flags' class='theme-hidden' value='$flags' />";
