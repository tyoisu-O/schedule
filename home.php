<?php
ini_set( 'session.gc_maxlifetime', 24*60*60 );
ini_set( 'session.gc_probability', 1 );
ini_set( 'session.gc_divisor', 1 );
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
    $session_user_info = [];
    $session_user_info[] = strval($count);
    $session_user_info[] = $_SESSION['name'];
    $session_user_info[] = $_SESSION['pass'];

    $_SESSION['login_name'] = $session_user_info;
    $login = 'Yes';
}


if (!empty($_SESSION['login_name'])) {
    $user_info = $_SESSION['login_name'];
    $login = 'Yes';
}

$day = date(Y) . '年' . date(n) . '月' . date(j) . '日';
$today = date(Y).date(m).date(d);




if (!empty($_POST['decision']) || !empty($_POST['add_sche'])) {
    $add = false;
    $new = true;
    $make_sche = false;
    if (!empty($_POST['sche_name']) && !empty($_POST['start_hour']) && !empty($_POST['start_minute']) && !empty($_POST['end_hour']) && !empty($_POST['end_minute'])) {
        $make_sche = true;


        $start_time = $_POST['start_hour'] . ':' . $_POST['start_minute'];
        $end_time =  $_POST['end_hour'] . ':' . $_POST['end_minute'];
        $sche_name = $_POST['sche_name'];

        $user_sche_data = fopen("./user_data/schedule_data.txt", 'r');
        while ($one_schedule = fgets($user_sche_data)) {
            $user_schedule = explode('$', $one_schedule);
            $user_number = $user_schedule[0];
            $user_day_schedule = $user_schedule[1];

            if ($_SESSION['login_name'][0] === $user_number) {
                $day_schedule = explode('/', $user_day_schedule);
                $schedule_day = $day_schedule[0];
                $schedule_start_time = $day_schedule[1];
                $schedule_name = $day_schedule[2];
                $schedule_end_time = $day_schedule[3];

                if ($today === $schedule_day) {
                    $new = false;
                    fclose($user_sche_data);

                    $overwrite_data = fopen("./user_data/schedule_data.txt", 'r');
                    $schedules = [];
                    while ($schedules[] = fgets($overwrite_data)) {
                    }
                    fclose($overwrite_data);

                    $overwrite_data = fopen("./user_data/schedule_data.txt", 'w');
                    foreach ($schedules as $schedule) {
                        $_user_schedule = explode('$', $schedule);
                        $_user_number = $_user_schedule[0];
                        $_user_day_schedule = $_user_schedule[1];

                        if ($_SESSION['login_name'][0] === $_user_number) {
                            $_day_schedule = explode('/', $_user_day_schedule);
                            $_schedule_day = $_day_schedule[0];

                            if ($today === $_schedule_day) {
                                //スケジュールの追加
                                $schedule = rtrim($schedule);
                                fwrite($overwrite_data, $schedule . '/' . $start_time . '/' . $sche_name . '/' . $end_time . "\n");
                                $add = true;
                            }
                        }
                        if (!$add) {
                            //スケジュールの再記入
                            if ($schedule) { //このif文でなぜかできてしまうbool(false)を書かない
                                fwrite($overwrite_data, $schedule);
                            } 
                        }
                        $add = false;
                    }
                    fclose($overwrite_data);
                    break;
                }
            } 
        }
        if ($new) {
            fclose($user_sche_data);

            $add_schedule = fopen("./user_data/schedule_data.txt", 'a');
            fwrite($add_schedule, "\n" . $_SESSION["login_name"][0] . '$' . $today . '/' . $start_time . '/' . $sche_name . '/' . $end_time);
            fclose($add_schedule);
        }
        if (!empty($_POST['add_sche'])) {
            $login = 'make';
        }
    } else {
        $login = 'make';
    }
}


$_sche_datas = file("./user_data/schedule_data.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$re_data = fopen("./user_data/schedule_data.txt", 'w');
foreach ($_sche_datas as $sche_data) {
    fwrite($re_data, $sche_data . "\n");
}
fclose($re_data);



if ($login === 'Yes') {
    $datas = fopen("./user_data/schedule_data.txt", 'r');
    while($schedule_data = fgets($datas)){
        $users_data = explode("$",$schedule_data);
        $user_num = $users_data[0];
        $schedule_datas = explode("/", $users_data[1]);
        //erorr : 新規でのログイン時,スケジュールが存在しても表示できない
        //解決 : 型が違った
        if ($user_num === $user_info[0]) {
            if ($today === $schedule_datas[0]) {
                $today_schedule = $schedule_datas;
                break;
            }
        }
    }
    

    if($today_schedule){
        array_shift($today_schedule);
    } else {
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

if (empty($_SESSION['login_name'])) {
    $login = 'No';
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
        <div class="wrapper">
            <header>
                <input type="submit" value="Web Schedule">
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
                    <div class="sche_make">
                        <?php if ($make_sche === false): ?>
                            <h4>スケジュール情報が抜けている為、登録されませんでした。</h4>
                        <?php endif ?>
                            <input type="text" class="sche_name" name="sche_name" placeholder="スケジュールの名前">
                            <div class="time_set">
                            <input type="text" class="start_hour" name="start_hour" placeholder="時">
                            <p>:</p>
                            <input type="text" class="start_minute" name="start_minute" placeholder="分">
                            <p>　〜　</p>
                            <input type="text" class="end_hour" name="end_hour" placeholder="時">
                            <p>:</p>
                            <input type="text" class="end_minute" name="end_minute" placeholder="分">
                        </div>
                        <div class="sche_btns">
                            <input type="submit" class="add_sche" name="add_sche" value="次のスケジュール">
                            <input type="submit" class="decision" name="decision" value="確定">
                        </div>  
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
                        <input type="submit" class="log_new" name="return" value="戻る">
                    </div>
                <?php endif ?>
                <?php if (!empty($_POST['make'])): ?>

                <?php endif ?>
            </main>
            <footer>
                <p>Webスケジュール管理ページ</p>
            </footer>
        </div> 
    </form>
</body>
</html>