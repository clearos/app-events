<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'events';
$app['version'] = '2.3.0';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('events_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('events_app_name');
$app['category'] = lang('base_category_reports');
$app['subcategory'] = lang('base_subcategory_performance_and_resources');

$app['tooltip'] = array(
    lang('events_tooltip_severity'),
    lang('events_tooltip_info'),
    lang('events_tooltip_warning'),
    lang('events_tooltip_critical'),
);

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_obsoletes'] = array(
    'app-clearsync-core',
);

$app['core_requires'] = array(
    'clearsync',
    'csplugin-filewatch',
    'csplugin-events => 1.0-24',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/events' => array(),
    '/var/clearos/events/onboot' => array(),
);

$app['core_file_manifest'] = array(
    'clearsync.php'=> array('target' => '/var/clearos/base/daemon/clearsync.php'),
    'trigger' => array(
        'target' => '/usr/sbin/trigger',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
    'events.conf' => array(
        'target' => '/etc/clearos/events.conf',
        'mode' => '0644',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
    'events.cron' => array(
        'target' => '/etc/cron.d/app-events',
        'mode' => '0644',
        'owner' => 'root',
        'group' => 'root',
    ),
    'events-notification' => array(
        'target' => '/usr/sbin/events-notification',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
    'zbootevent.init' => array(
        'target' => '/etc/rc.d/init.d/zbootevent',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
    ),
    'filewatch-events-configuration.conf'=> array('target' => '/etc/clearsync.d/filewatch-events-configuration.conf'),
);

/////////////////////////////////////////////////////////////////////////////
// Dashboard Widgets
/////////////////////////////////////////////////////////////////////////////

$app['dashboard_widgets'] = array(
    $app['category'] => array(
        'events/events_dashboard/last_24' => array(
            'title' => lang('events_last_24_hours'),
            'restricted' => FALSE,
        )
    )
);

/////////////////////////////////////////////////////////////////////////////
// App Removal Dependencies
/////////////////////////////////////////////////////////////////////////////

$app['delete_dependency'] = array();
