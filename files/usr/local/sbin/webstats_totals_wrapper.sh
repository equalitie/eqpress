#!/bin/sh
export PATH=/usr/bin:/bin
HTML=/var/tmp/webstats.html
cat > ${HTML} <<EOM
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title></title></head><body>
<div style="white-space:nowrap"><pre>

EOM
php /usr/local/sbin/webstats_totals.php >> ${HTML}
cat >> ${HTML} <<EOMM

</pre></div></body></html>
EOMM

echo "Today's webstats" | mail -s "easyPress web stats" -a /var/tmp/webstats.html {{ monitoring_email }}
