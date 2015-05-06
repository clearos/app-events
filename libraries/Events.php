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

clearos_load_language('base');
clearos_load_language('events');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Classes
//--------

use \clearos\apps\base\Configuration_File as Configuration_File;
use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\File as File;
use \clearos\apps\events\SSP as SSP;
use \clearos\apps\mail_notification\Mail_Notification as Mail_Notification;
use \clearos\apps\network\Hostname as Hostname;

clearos_load_library('base/Configuration_File');
clearos_load_library('base/Engine');
clearos_load_library('base/File');
clearos_load_library('events/SSP');
clearos_load_library('mail_notification/Mail_Notification');
clearos_load_library('network/Hostname');

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
    const FILE_CONFIG = '/etc/clearos/events.conf';
    const INSTANT_NOTIFICATION = 1;
    const DAILY_NOTIFICATION = 2;
    const FLAG_INFO = 1;
    const FLAG_WARN = 2;
    const FLAG_CRIT = 4;
    const FLAG_ALL = 65535;

    ///////////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////////

    protected $db_handle = NULL;
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
     * Set the status of the monitor.
     *
     * @param boolean $status live monitoring status
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_status($status));

        $this->_set_parameter('status', $status);
    }

    /**
     * Set the autopurge time.
     *
     * @param int $autopurge autopurge
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_autopurge($autopurge)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_autopurge($autopurge));

        $this->_set_parameter('autopurge', $autopurge);
    }

    /**
     * Set the status of the instant notifiation email.
     *
     * @param boolean $status instant notifications via email
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_instant_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_status($status));

        $this->_set_parameter('instant_status', $status);
    }

    /**
     * Set the instant threshold.
     *
     * @param bool $info info
     * @param bool $warn warning
     * @param bool $crit critical
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_instant_threshold($info, $warn, $crit)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_threshold($info));
        Validation_Exception::is_valid($this->validate_threshold($warn));
        Validation_Exception::is_valid($this->validate_threshold($crit));

        $value = 0;
        if ($info)
            $value += self::FLAG_INFO;
        if ($warn)
            $value += self::FLAG_WARN;
        if ($crit)
            $value += self::FLAG_CRIT;

        $this->_set_parameter('instant_threshold', $value);
    }

    /**
     * Set the instant email.
     *
     * @param int $email email address for instant notifications
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_instant_email($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_email($email));

        $this->_set_parameter('instant_email', preg_replace("/\n/",",", $email));
    }

    /**
     * Set the status of the daily notifiation email.
     *
     * @param boolean $status daily notifications via email
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_daily_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_status($status));

        $this->_set_parameter('daily_status', $status);
    }

    /**
     * Set the daily threshold.
     *
     * @param bool $info info
     * @param bool $warn warning
     * @param bool $crit critical
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_daily_threshold($info, $warn, $crit)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_threshold($info));
        Validation_Exception::is_valid($this->validate_threshold($warn));
        Validation_Exception::is_valid($this->validate_threshold($crit));

        $value = 0;
        if ($info)
            $value += self::FLAG_INFO;
        if ($warn)
            $value += self::FLAG_WARN;
        if ($crit)
            $value += self::FLAG_CRIT;

        $this->_set_parameter('daily_threshold', $value);
    }

    /**
     * Set the daily email.
     *
     * @param int $email email address for daily notifications
     *
     * @return void
     * @throws Validation_Exception
     */

    function set_daily_email($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        Validation_Exception::is_valid($this->validate_email($email));

        $this->_set_parameter('daily_email', preg_replace("/\n/",",", $email));
    }

    /**
     * Get the monitoring status.
     *
     * @return boolean
     */

    function get_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['status'];
    }

    /**
     * Get the monitoring autopurge.
     *
     * @return boolean
     */

    function get_autopurge()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['autopurge'];
    }

    /**
     * Get the instant notification status.
     *
     * @return boolean
     */

    function get_instant_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['instant_status'];
    }

    /**
     * Get the instant notification threshold.
     *
     * @return array
     */

    function get_instant_threshold()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $threshold = array(
            ($this->config['instant_threshold'] & self::FLAG_INFO),
            ($this->config['instant_threshold'] & self::FLAG_WARN),
            ($this->config['instant_threshold'] & self::FLAG_CRIT),
        );

        return $threshold;
    }

    /**
     * Get the instant email notification.
     *
     * @return string
     */

    function get_instant_email()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return explode(',', $this->config['instant_email']);
    }

    /**
     * Get the daily notification status.
     *
     * @return boolean
     */

    function get_daily_status()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return $this->config['daily_status'];
    }

    /**
     * Get the daily notification threshold.
     *
     * @return array
     */

    function get_daily_threshold()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        $threshold = array(
            ($this->config['daily_threshold'] & self::FLAG_INFO),
            ($this->config['daily_threshold'] & self::FLAG_WARN),
            ($this->config['daily_threshold'] & self::FLAG_CRIT),
        );

        return $threshold;
    }

    /**
     * Get the daily email notification.
     *
     * @return string
     */

    function get_daily_email()
    {
        clearos_profile(__METHOD__, __LINE__);

        if (!$this->is_loaded)
            $this->_load_config();

        return explode(',', $this->config['daily_email']);
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

        $sql = 'SELECT * FROM alerts' . $where . " ORDER BY id DESC" . $limit;

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

    /**
    * Sends a notification email.
    *
    * @param int    $type type of notification
    * @param String $date date of daily notification, if applicable
    *
    * @return void
    * @throws Engine_Exception
    */

    function send_notification($type, $date = NULL)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($type == self::INSTANT_NOTIFICATION) {
            if (!$this->get_instant_status() || !$this->get_instant_email())
                return;
            $email_list = $this->get_instant_email();
        } else if ($type == self::DAILY_NOTIFICATION) {
            if (!$this->get_daily_status() || !$this->get_daily_email())
                return;
            $email_list = $this->get_daily_email();
        }

        $mailer = new Mail_Notification();
        $hostname = new Hostname();
        $date_obj = \DateTime::createFromFormat('d-m-Y', $date);
        $subject = lang('events_event_notification') . ' - ' . $hostname->get() . ($type == self::DAILY_NOTIFICATION ? " (" . $date_obj->format('M j, Y') . ")" : "");
        $body = "<table cellspacing='0' cellpadding='8' border='0' style='font: Arial, sans-serif;'>\n";
        $body .= "  <tr>\n";
        $body .= "    <th style='text-align: center;'></th>" .
                 "    <th style='text-align: left;'>" . lang('base_description') . "</th>" .
                 "    <th style='text-align: left;'>" . lang('events_type') . "</th>" .
                 "    <th style='text-align: left;'>" . lang('base_timestamp') . "</th>\n";
        $body .= "  <tr>\n";
        $events = $this->get_events();
        $counter = 0;

        foreach ($events['events'] as $event) {
            $colour = '#608921'; 
            if ($event['flags'] & 2)
                $colour = '#f39c12'; 
            else if ($event['flags'] & 4)
                $colour = '#dd4b39'; 
            $body .= "  <tr style='background-color: " . ($counter % 2 ? "#f5f5f5" : "#fff") . ";'>\n";
            $body .= "    <td width='2%' style='border-top: 1px solid #ddd; text-align: center;'><span style='color: $colour;'>&#x2b24;</span></td>\n" .
                     "    <td width='58%' style='border-top: 1px solid #ddd; text-align: left;'>" . $event['desc'] . "</td>\n" .
                     "    <td width='15%' style='border-top: 1px solid #ddd; text-align: left;'>" . $event['type'] . "</td>\n" .
                     "    <td width='25%' style='border-top: 1px solid #ddd; text-align: left;'>" . date('Y-m-d H:i:s', strftime($event['stamp'])) . "</td>\n";
            $body .= "  </tr>\n";
            $counter++;
        }
        $body .= "</table>\n";

        

        foreach ($email_list as $email)
            $mailer->add_recipient($email);
        $mailer->set_message_subject($subject);
        $mailer->set_message_html_body($body);

        $mailer->send();
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

    /**
     * Validation routine for status.
     *
     * @param boolean $status status
     *
     * @return mixed void if status is valid, errmsg otherwise
     */

    public function validate_status($status)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for autopurge.
     *
     * @param boolean $autopurge autopurge
     *
     * @return mixed void if autopurge is valid, errmsg otherwise
     */

    public function validate_autopurge($autopurge)
    {
        clearos_profile(__METHOD__, __LINE__);
        $list = $this->get_autopurge_options();
        if (!array_key_exists($autopurge, $list))
            return lang('events_autopurge')  . ' - ' . lang('base_invalid');
    }

    /**
     * Validation routine for instant status notifications.
     *
     * @param boolean $instant instant notifications
     *
     * @return mixed void if instant is valid, errmsg otherwise
     */

    public function validate_instant_status($instant)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for daily status notifications.
     *
     * @param boolean $daily daily notifications
     *
     * @return mixed void if daily is valid, errmsg otherwise
     */

    public function validate_daily_status($daily)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for threshold.
     *
     * @param boolean $threshold threshold
     *
     * @return mixed void if threshold is valid, errmsg otherwise
     */

    public function validate_threshold($threshold)
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Validation routine for email.
     *
     * @param array $email email array
     *
     * @return mixed void if email is valid, errmsg otherwise
     */

    public function validate_email($email)
    {
        clearos_profile(__METHOD__, __LINE__);

        $emails = explode("\n", $email);
        foreach ($emails as $email) {
            if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email))
                return lang('base_email_address_invalid');
        }
    }

}
