<?php

/**
 * Lst 24 Hour summary view.
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

$link = anchor_view('/app/events', 'high', array('class' => 'pull-right'));

echo box_open(lang('events_last_24_hours'));
echo box_content_open();
// Informational
echo row_open();
echo column_open(9);
echo icon('critical', array('class' => 'theme-text-info')) . '&nbsp;&nbsp;' . lang('events_info');
echo column_close();
echo column_open(3);
echo $summary['info'];
echo column_close();
echo row_close();

// Warnings
echo row_open();
echo column_open(9);
echo icon('critical', array('class' => 'theme-text-warning')) . '&nbsp;&nbsp;' . lang('events_warning');
echo column_close();
echo column_open(3);
echo $summary['warning'];
echo column_close();
echo row_close();

// Critial
echo row_open();
echo column_open(9);
echo icon('critical', array('class' => 'theme-text-alert')) . '&nbsp;&nbsp;' . lang('events_critical');
echo column_close();
echo column_open(3);
echo $summary['critical'];
echo column_close();
echo row_close();

echo box_content_close();
echo box_footer('footer-events', $link, array('class' => 'theme-clear'));
echo box_close();
