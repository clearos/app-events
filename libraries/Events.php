<?php

/**
 * Events class.
 *
 * @category   apps
 * @package    events
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/events/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\events;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('events');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\Events\SSP as SSP;
use \clearos\apps\base\File as File;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('events/SSP');
clearos_load_library('base/File');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Events class.
 *
 * @category   apps
 * @package    events
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/events/
 */

class Events extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const DB_CONN = '/var/lib/csplugin-sysmon/sysmon.db';
    const FLAG_INFO = 1;
    const FLAG_WARN = 2;
    const FLAG_CRIT = 4;
    const FLAG_ALL = 65535;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $config = NULL;
    protected $is_loaded = FALSE;

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Events constructor.
     */

    function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Get auto purge threshold options.
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_autopurge_options()
    {
        clearos_profile(__METHOD__, __LINE__);

        $options = array (
            10 => lang('events_older_than_1_day'),
            20 => lang('events_older_than_1_week'),
            30 => lang('events_older_than_1_month'),
            40 => lang('events_older_than_3_months'),
            50 => lang('events_older_than_6_months'),
            60 => lang('events_older_than_1_year')
        );
        return $options;
    }

    /**
     * Get events.
     *
     *
     * @param int $filter filter for flags
     * @param int $limit  limit number of records returned
     *
     * @return array
     * @throws Engine_Exception
     */

    function get_events($filter = self::FLAG_ALL, $limit = 0)
    {
        clearos_profile(__METHOD__, __LINE__);

        $this->_get_db_handle();

        $result = array();

        // Run query
        //----------

        $where = '';
        if ($filter != self::FLAG_ALL) {
            $flags_filter = array();
            if ($filter & self::FLAG_INFO)
                $flags_filter[] = 'flags & ' . self::FLAG_INFO;
            if ($filter & self::FLAG_WARN)
                $flags_filter[] = 'flags & ' . self::FLAG_WARN;
            if ($filter & self::FLAG_CRIT)
                $flags_filter[] = 'flags & ' . self::FLAG_CRIT;
			$where = ' WHERE ('. implode(' OR ', $flags_filter) . ')';
        }
        if ($limit == 0)
            $limit = '';
        else
            $limit = " LIMIT $limit";

        $sql = 'SELECT * FROM alerts' . $where . $limit;

        try {
            $dbs = $this->db_handle->prepare($sql);
            $dbs->execute();

            $result['events'] = $dbs->fetchAll(\PDO::FETCH_ASSOC);

            $sql = 'SELECT count(id) AS total FROM alerts' . $where;
            $dbs = $this->db_handle->prepare($sql);
            $dbs->execute();

            $result['total'] = $dbs->fetch()[0];

        } catch(\Exception $e) {
            throw new Engine_Exception($e->getMessage());
        }

        return $result;
    }

    ///////////////////////////////////////////////////////////////////////////////
    // P R I V A T E    R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

    /**
    * Loads configuration files.
    *
    * @return void
    * @throws Engine_Exception
    */

    protected function _load_config()
    {
        clearos_profile(__METHOD__, __LINE__);

        $configfile = new Configuration_File(self::FILE_CONFIG);

        $this->config = $configfile->load();

        $this->is_loaded = TRUE;
    }

    /**
     * Generic set routine.
     *
     * @param string $key   key name
     * @param string $value value for the key
     *
     * @return  void
     * @throws Engine_Exception
     */

    private function _set_parameter($key, $value)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $file = new File(self::FILE_CONFIG, TRUE);
            $match = $file->replace_lines("/^$key\s*=\s*/", "$key = $value\n");

            if (!$match)
                $file->add_lines("$key = $value\n");
        } catch (Exception $e) {
            throw new Engine_Exception(clearos_exception_message($e), CLEAROS_ERROR);
        }

        $this->is_loaded = FALSE;
    }

    /**
     * Creates a db handle.
     *
     * @return void
     */

    protected function _get_db_handle()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (! is_null($this->db_handle))
            return;

        // Get a connection
        //-----------------

        try {
			$this->db_handle = new \PDO(
				"sqlite:" . self::DB_CONN,
				NULL, NULL,
				array( \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION )
			);
        } catch(\PDOException $e) {
            throw new Engine_Exception($e->getMessage());
        }
    }

    ///////////////////////////////////////////////////////////////////////////////
    // V A L I D A T I O N   R O U T I N E S
    ///////////////////////////////////////////////////////////////////////////////

}
