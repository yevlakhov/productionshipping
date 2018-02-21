#!/bin/bash

current_dir="$(dirname $(readlink -f $0))";
php "$current_dir"/send_manuf_report.php > "$current_dir"/cron.log  2>&1
php "$current_dir"/send_cron_log.php > /dev/null 2>&1