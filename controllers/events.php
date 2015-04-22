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

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\events\SSP as SSP;

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

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

class Events extends ClearOS_Controller
{

    /**
     * Events default controller
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->load->library('events/Events');
        $this->lang->load('events');

        // Load form data
        //---------------
        $data = array();

        $this->page->view_form('events/summary', $data, lang('events_app_name'));
    }

    /**
     * Ajax events info
     *
     * @return JSON
     */

    function get_info()
    {
        clearos_profile(__METHOD__, __LINE__);

        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        try {
            $this->load->library('events/Events');
            $this->load->library('events/SSP');

            $sql_details = array(
                'path' => '/var/lib/csplugin-sysmon/sysmon.db'
            );
            $table = 'alerts';
            $primaryKey = 'id';
            $columns = array(
                array( 'db' => 'flags', 'dt' => 0,
                    'formatter' => function( $d, $row ) {
                        return "<i class='fa fa-gears'></i>";
                    }
                ),
                array( 'db' => 'desc', 'dt' => 1 ),
                array( 'db' => 'type',  'dt' => 2 ),
                array( 'db' => 'stamp',  'dt' => 3,
                    'formatter' => function( $d, $row ) {
                        return date( 'M y', strftime($d));
                    }
                ),
                array( 'db' => 'type',  'dt' => 4,
                    'formatter' => function( $d, $row ) {
                        return anchor_add('/obb');
                    }
                )
            );

            parse_str($_SERVER['QUERY_STRING'], $get_params);

            echo json_encode(
                SSP::simple($get_params, $sql_details, $table, $primaryKey, $columns )
            );

        } catch (Exception $e) {
            echo json_encode(Array('code' => clearos_exception_code($e), 'errmsg' => clearos_exception_message($e)));
        }
    }
}
