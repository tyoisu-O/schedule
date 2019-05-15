<?php
    $day = date(Y) . '年' . date(n) . '月' . date(j) . '日';
    $today = date(Y).date(n).date(j);
    $datas = fopen("./user_data/example_user.txt", 'r');
    while($schedule_data = fgets($datas)){
        $schedule_data = explode("/", $schedule_data);
        if ($today === $schedule_data[0]) {
            $today_schedule = $schedule_data;
        }
    }
    array_shift($today_schedule);
 ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="schedule.css">
    <title>Schedule</title>
</head>
<body>
    <header>
        <h1>Today Schedule</h1>
    </header>
    <main>
        <h2 class="today"><?php echo $day; ?></h2>
        <?php foreach($today_schedule as $schedule): ?>
        <p><?php echo $schedule ?></p>
        <?php endforeach ?>
    </main>
    <footer>
        <p>スケジュール管理webページ</p>
    </footer>
</body>
</html>