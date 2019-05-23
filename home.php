<?php
session_start();

if (!empty($_POST['log_out'])) {
    unset($_SESSION['login_name']);
}


$login = 'No';
$output = '';

if (!empty($_POST['user_pass']) && !empty($_POST['user_name'])){

    $_SESSION['name'] = $_POST['user_name'];
    $_SESSION['pass'] = $_POST['user_pass'];

    $login = 'New';
    $pass_data = fopen("./user_data/user_pass.txt", 'r');
    while($user_info = fgets($pass_data)){
        $user_info = explode("/", $user_info);
        
        $user_info[2] = trim($user_info[2]);

        if ($_POST['user_name'] === $user_info[1] && $_POST['user_pass'] === $user_info[2]) {
            $login = 'Yes';
            $_SESSION['login_name'] = $user_info;
            break;
        }
    }
    fclose($pass_data);
}


if(!empty($_POST['new'])) {
    $pass_data = fopen("./user_data/user_pass.txt", 'r');
    $count = 1;
    while($user_count = fgets($pass_data)){
        $count++;
    }
    $new_user_pass_data = $count . '/' . $_SESSION['name'] . '/' . $_SESSION['pass'];
    fclose($pass_data);

    $pass_data = fopen("./user_data/user_pass.txt", 'a');
    fwrite($pass_data, "\n" . $new_user_pass_data);
    $_SESSION['login_name'] = [$count, $_SESSION['name'], $_SESSION['pass']];
    $login = 'Yes';
}


if (!empty($_SESSION['login_name'])) {
    $user_info = $_SESSION['login_name'];
    $login = 'Yes';
}

$day = date(Y) . '年' . date(n) . '月' . date(j) . '日';

if ($login === 'Yes') {
    $today = date(Y).date(n).date(j);
    $datas = fopen("./user_data/schedule_data.txt", 'r');
    while($schedule_data = fgets($datas)){
        $users_data = explode(" ",$schedule_data);
        $user_num = $users_data[0];
        $schedule_data = explode("/", $users_data[1]);
        if ($user_num === $user_info[0]) {
            if ($today === $schedule_data[0]) {
                $today_schedule = $schedule_data;
                break;
            }
        }
    }
    

    if($today_schedule){
        array_shift($today_schedule);
    } else{
        $output = 'No Schedule';
    }

    $schedule_count = count($today_schedule);


    $output_schedules =[];
    for ($i = 1; $i <= $schedule_count; $i += 3){
        $time = $today_schedule[$i - 1];
        $action = $today_schedule[$i];
        $end_time = $today_schedule[$i + 1];
        $one_schedule = [$time, $action, $end_time];

        $output_schedules[] = $one_schedule;
    }
}

if (!empty($_POST['make'])) {
    $login  =  'make';
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- <meta http-equiv="refresh" content="10; URL="> -->
    <link rel="stylesheet" type="text/css" href="schedule.css">
    <title>Schedule</title>
</head>
<body>
    <form action="home.php" method="post">  
        <header>
            <h1>Web Schedule</h1>
            <?php if(!empty($_SESSION['login_name'])): ?>
                <p><?php echo $_SESSION['login_name'][1] ?>さん</p>
            <?php endif ?>
        </header>
        <nav>
            <?php if ($login === 'Yes' ||  $login === 'make'): ?>
                <input type="submit" class="make" name="make" value="スケジュール作成">
                <input type="submit" class="log_out" name="log_out" value="ログアウト">
            <?php endif ?>
        </nav>
        <main>
            <h2 class="today">● <?php echo $day; ?> ●</h2>
            <?php if ($login === 'Yes' && empty($_POST['make'])): ?>
                <?php foreach($output_schedules as $one_schedule): ?>
                    <div class="one_schedule">
                        <h3 class="first"><?php echo $one_schedule[0]; ?> 〜</h3>
                        <p><?php echo $one_schedule[1]; ?></p>
                        <h3 class="end">〜 <?php echo $one_schedule[2]; ?></h3>
                    </div>
                <?php endforeach ?>
                <?php if($output === 'No Schedule'): ?>
                    <div class="No_Schedule">
                        <h3>スケジュールが設定されていません</h3>
                        <input type="submit" class="make2" name="make" value="スケジュール作成">
                    </div>
                <?php endif ?>
            <?php elseif($login === 'make'): ?>
                <!-- <div class="time_set">
                    <input type="time" name="" value="<?php echo time(H); ?>:<?php echo time(i); ?>" min="00：00" max="24：00">
                    <p> 〜 </p>
                    <input type="time" name="" value="00:00" min="00：00" max="24：00">
                </div> -->
                <div class="sche_make">
                    <input type="text" class="sche_name" name="sche_name" placeholder="スケジュールの名前">
                    <input type="submit" class="decision" name="decision" value="確定">
                </div>
            <?php elseif($login === 'No'): ?>
                <div class="login">
                    <h3>ログイン&新規登録</h3>
                    <div class="form_textbox">
                        <input type="text" class="user_name" name="user_name" placeholder="ニックネーム">
                        <input type="text" class="user_pass" name="user_pass" placeholder="パスワード">
                    </div>
                    <input type="submit" class="log_new" name="log" value="I N">
                </div>
            <?php elseif($login === 'New'): ?>
                <div class="login">
                    <h3>ログイン&新規登録</h3>
                    <div class="new_user_info">
                        <h4 class="new_name">ニックネーム:<?php echo $_SESSION['name'] ?></h4>
                        <h4 class="new_pass">パスワード:<?php echo $_SESSION['pass'] ?></h4>
                    </div>
                    <input type="submit" class="log_new" name="new" value="新規登録">
                </div>
            <?php endif ?>
            <?php if (!empty($_POST['make'])): ?>

            <?php endif ?>
        </main>
        <footer>
            <p>Webスケジュール管理ページ</p>
        </footer>
        <input type="submit" class="kousin" name="kousin" value="更新">
    </form>
</body>
</html>