<?php

/**
 * Javascript helper for Events.
 *
 * @category   apps
 * @package    events
 * @subpackage javascript
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

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('events');
clearos_load_language('base');

///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////

header('Content-Type: application/x-javascript');

?>
var lang_error = '<?php echo lang('base_error'); ?>';
var lang_show_info = '<?php echo lang('events_show_info'); ?>';
var lang_show_warning = '<?php echo lang('events_show_warning'); ?>';
var lang_show_critical = '<?php echo lang('events_show_critical'); ?>';

$(document).ready(function() {
    $('#events_list').on('draw.dt', function () {
        // Hack..FIXME...aligns icons up to look a bit better
        $('#events_list tr td:first-child').css('padding', '8px 0px 8px 15px');
    });
    var flags = $('#flags').val();
    // Critical
    if (flags & 4)
        checked = 'checked';
    else
        checked = '';
    clearos_add_sidebar_pair(lang_show_critical, '<input type="checkbox" name="flags_critical" value="4" class="flags_select" ' + checked + '/>');
    // Warning 
    if (flags & 2)
        checked = 'checked';
    else
        checked = '';
    clearos_add_sidebar_pair(lang_show_warning, '<input type="checkbox" name="flags_warning" value="2" class="flags_select" ' + checked + '/>');
    // Info 
    if (flags & 1)
        checked = 'checked';
    else
        checked = '';
    clearos_add_sidebar_pair(lang_show_info, '<input type="checkbox" name="flags_info" value="1" class="flags_select" ' + checked + '/>');
    $('.flags_select').on('click', function () {
        set_flags();
    });

    toggle_fields();
    $('.form-control').on('change', function () {
        toggle_fields();
    });
});

function set_flags() {
    var flags = 0;
    $('.flags_select').each(function(index) {
        if ($(this).prop('checked'))
            flags += parseInt($(this).val());
    });
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '/app/events/flags/' + flags,
        success: function(json) {
            if (json.code == 0) {
                // Redraw the table
                var events = get_table_events_list();
                events.fnDraw();
            } else {
                clearos_dialog_box('error', lang_warning, json.errmsg);
            }
        },
        error: function(xhr, text, err) {
            clearos_dialog_box('error', lang_warning, xhr.responseText.toString());
        }
    });
}

function toggle_fields() {
    if ($('#status').val() == 1) {
        $('.status-required').attr('disabled', false);
        if ($('#instant_status').val() == 1) {
            $('.instant-required').attr('disabled', false);
        } else {
            $('.instant-required').attr('disabled', true);
            $('.instant-required').attr('checked', false);
            $('.instant-required').val('');
        }
        if ($('#daily_status').val() == 1) {
            $('.daily-required').attr('disabled', false);
        } else {
            $('.daily-required').attr('disabled', true);
            $('.daily-required').attr('checked', false);
            $('.daily-required').val('');
        }
    } else {
        $('.status-required').attr('disabled', true);
    }
}

// vim: syntax=javascript ts=4
