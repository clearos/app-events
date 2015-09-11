<?php

/**
 * Event utilities class.
 *
 * @category   apps
 * @package    events
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
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

use \clearos\apps\base\Engine as Engine;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\events\Events as Events;

clearos_load_library('events/Events');
clearos_load_library('base/Engine');
clearos_load_library('base/Shell');

// Exceptions
//-----------

use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Event utilities class.
 *
 * General utilities used in dealing with the events.
 *
 * @category   apps
 * @package    events
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2015 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/events/
 */

class Event_Utils extends Engine
{
    ///////////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////////

    const COMMAND_EVENTS_CTRL = '/usr/bin/eventsctl';

    ///////////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Event constructor.
     *
     * @return void
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);
    }

    /**
     * Add an event to the events database.
     *
     * @param string $description  description
     * @param string $severity     severity
     * @param string $type         type
     * @param string $basename     basename
     * @param bool   $auto_resolve auto resolve
     * @param string $user         user
     * @param string $uuid         UUID
     * @param string $origin       origin
     *
     */

    public static function add_event(
        $description = NULL, $severity = NULL, $type = NULL, $basename = NULL,
        $auto_resolve = FALSE, $user = NULL, $uuid = NULL, $origin = NULL
    )
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($basename == NULL) {
            // Bit of a hack to get basename, but we may need basename to create unique UUID for events
            // using the common TYPE_DEFAULT type
            list(, $caller) = debug_backtrace(FALSE);
            if (preg_match('/.*\\\apps\\\([a-z\_]+)\\\.*/', $caller['class'], $match))
                $basename = $match[1];
        }

        try {
            if (Event_Utils::is_valid_description($description) === FALSE)
                throw new Validation_Exception(lang('events_description_invalid'));

            if (Event_Utils::is_valid_type($type) === FALSE)
                throw new Validation_Exception(lang('events_type_invalid'));

            if (Event_Utils::is_valid_severity($severity) === FALSE)
                throw new Validation_Exception(lang('events_severity_invalid'));

            if ($severity == NULL)
                $severity = '-l ' . Events::SEVERITY_INFO;
            else
                $severity = '-l ' . $severity;

            if ($type == NULL) {
                $type = '-t ' . Events::TYPE_DEFAULT;
                // If using the default type, set uuid to unique ID, otherwise, they will override each other
                $uuid = md5($basename . $description);
            } else {
                $type = '-t ' . $type;
            }

            if (Event_Utils::is_valid_basename($basename) === FALSE)
                throw new Validation_Exception(lang('events_basename_invalid'));

            if ($basename == NULL)
                $basename = '';
            else
                $basename = '-b ' . $basename;

            if (Event_Utils::is_valid_user($user) === FALSE)
                throw new Validation_Exception(lang('events_user_invalid'));

            if ($user == NULL)
                $user = '';
            else
                $user = '-u ' . $user;

            if (Event_Utils::is_valid_uuid($uuid) === FALSE)
                throw new Validation_Exception(lang('events_uuid_invalid'));

            if ($uuid == NULL)
                $uuid = '';
            else
                $uuid = '-U ' . $uuid;

            if (Event_Utils::is_valid_origin($origin) === FALSE)
                throw new Validation_Exception(lang('events_origin_invalid'));

            if ($origin == NULL)
                $origin = '';
            else
                $origin = '-o ' . $origin;

            if ($auto_resolve === TRUE)
                $auto_resolve = '--auto-resolve';

            $shell = new Shell();
            $options = array('validate_exit_code' => FALSE);
            $exitcode = $shell->execute(
                self::COMMAND_EVENTS_CTRL,
                " -s $type $severity $basename $user $uuid $origin $auto_resolve " . escapeshellarg($description),
                TRUE,
                $options
            );

        } catch (\Exception $e) {
            clearos_log('events', "Failed to add log event: " . clearos_exception_message($e));
        }
        
    }

    /**
     * Resolve an event.
     *
     * @param string $type type
     *
     */

    public static function resolve_event($type)
    {
        clearos_profile(__METHOD__, __LINE__);

        try {
            $shell = new Shell();
            $options = array('validate_exit_code' => FALSE);
            $exitcode = $shell->execute(
                self::COMMAND_EVENTS_CTRL,
                " -r -t $type",
                TRUE,
                $options
            );

        } catch (\Exception $e) {
            clearos_log('events', "Failed to resolve log event: " . clearos_exception_message($e));
        }
    }

    /**
     * Validates event type.
     *
     * @param string $type type of event
     *
     * @return string error message if type is invalid
     */

    public static function is_valid_type($type)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($type == NULL)
            return TRUE; 
        else if (!isset($type) || $type == '')
            return FALSE;

        return TRUE;
    }

    /**
     * Validates event description.
     *
     * @param string $description description of event
     *
     * @return string error message if description is invalid
     */

    public static function is_valid_description($description)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($description == NULL || $description == '')
            return FALSE; 

        return TRUE;
    }

    /**
     * Validates severity flag.
     *
     * @param string $severity severity
     *
     * @return string error message if severity is invalid
     */

    public static function is_valid_severity($severity)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($severity == NULL)
            return TRUE; 
        else if (!preg_match('/INFO|WARN|CRIT/', $severity))
            return FALSE;

        return TRUE;
    }

    /**
     * Validates basename.
     *
     * @param string $basename basename
     *
     * @return string error message if basename is invalid
     */

    public static function is_valid_basename($basename)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($basename == NULL)
            return TRUE; 
        else if (empty($basename))
            return FALSE;

        return TRUE;
    }

    /**
     * Validates origin.
     *
     * @param string $origin origin
     *
     * @return string error message if origin is invalid
     */

    public static function is_valid_origin($origin)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($origin == NULL)
            return TRUE; 
        else if (empty($origin))
            return FALSE;

        return TRUE;
    }

    /**
     * Validates user ID.
     *
     * @param string $user user
     *
     * @return string error message if user ID is invalid
     */

    public static function is_valid_user($user)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($user == NULL)
            return TRUE; 
        else if (empty($user))
            return FALSE;

        return TRUE;
    }

    /**
     * Validates uuid.
     *
     * @param String $uuid uuid
     *
     * @return string error message if uuid is invalid
     */

    public static function is_valid_uuid($uuid)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($uuid == NULL)
            return TRUE; 
        else if (!preg_match('/[a-z\_]+/', $uuid))
            return FALSE;

        return TRUE;
    }

}
