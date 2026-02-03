<?php


define('CLI_SCRIPT', true);
require_once('./config.php');

// Call 50 times and get an average time.
$total = 0;
for ($i=0; $i<50; $i++) {
    $start_time = hrtime(true);
    $activities = \local_gugrades\api::get_activities(2, 3);
    $end_time = hrtime(true);

    $elapsed = $end_time - $start_time;
    $total += $elapsed;
}

var_dump($activities);

echo "TIME " . number_format(floor($total / 50)) . "\n";
