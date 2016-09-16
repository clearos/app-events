
Name: app-events
Epoch: 1
Version: 2.3.1
Release: 1%{dist}
Summary: Events and Notifications
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
The Events and Notifications app provides a way for other apps to listen for events that occur on the system.  You can view them here and/or configure bulk reports or notifications to be sent to you via email.

%package core
Summary: Events and Notifications - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: clearsync
Requires: csplugin-filewatch
Requires: csplugin-events => 1.0-24
Obsoletes: app-clearsync-core

%description core
The Events and Notifications app provides a way for other apps to listen for events that occur on the system.  You can view them here and/or configure bulk reports or notifications to be sent to you via email.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/events
cp -r * %{buildroot}/usr/clearos/apps/events/

install -d -m 0755 %{buildroot}/var/clearos/events
install -d -m 0755 %{buildroot}/var/clearos/events/onboot
install -D -m 0644 packaging/clearsync.php %{buildroot}/var/clearos/base/daemon/clearsync.php
install -D -m 0755 packaging/events-notification %{buildroot}/usr/sbin/events-notification
install -D -m 0644 packaging/events.conf %{buildroot}/etc/clearos/events.conf
install -D -m 0644 packaging/events.cron %{buildroot}/etc/cron.d/app-events
install -D -m 0644 packaging/filewatch-events-configuration.conf %{buildroot}/etc/clearsync.d/filewatch-events-configuration.conf
install -D -m 0755 packaging/trigger %{buildroot}/usr/sbin/trigger
install -D -m 0755 packaging/zbootevent.init %{buildroot}/etc/rc.d/init.d/zbootevent

%post
logger -p local6.notice -t installer 'app-events - installing'

%post core
logger -p local6.notice -t installer 'app-events-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/events/deploy/install ] && /usr/clearos/apps/events/deploy/install
fi

[ -x /usr/clearos/apps/events/deploy/upgrade ] && /usr/clearos/apps/events/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-events - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-events-core - uninstalling'
    [ -x /usr/clearos/apps/events/deploy/uninstall ] && /usr/clearos/apps/events/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/events/controllers
/usr/clearos/apps/events/htdocs
/usr/clearos/apps/events/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/events/packaging
%dir /usr/clearos/apps/events
%dir /var/clearos/events
%dir /var/clearos/events/onboot
/usr/clearos/apps/events/deploy
/usr/clearos/apps/events/language
/usr/clearos/apps/events/libraries
/var/clearos/base/daemon/clearsync.php
/usr/sbin/events-notification
%attr(0644,webconfig,webconfig) %config(noreplace) /etc/clearos/events.conf
/etc/cron.d/app-events
/etc/clearsync.d/filewatch-events-configuration.conf
/usr/sbin/trigger
/etc/rc.d/init.d/zbootevent
