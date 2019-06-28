<?php
// やることリスト
// ・編集機能の追加(変更)
// ・スマホ版の特殊文字対応
// ・スマホ版のfont-size(改行対策)
// ・当日の主張css
// ・int(0)を入力するとエラーがでる問題対策
// ・ToDoリストシステムの作成

ini_set( 'session.gc_maxlifetime', 24*60*60 );
ini_set( 'session.gc_probability', 1 );
ini_set( 'session.gc_divisor', 1 );
session_start();


if (!empty($_POST['log_out'])) { 
    unset($_SESSION['login_name']);
    unset($_SESSION['day_move']);
    unset($_SESSION['month_move']);
    unset($_SESSION['year_move']);
    unset($_SESSION['edit_contents']); // 仮
}

$fix = false; //手動でboolean

$login = 'No';
$output = '';

if (!empty($_POST['user_pass']) && !empty($_POST['user_name'])){

    $_SESSION['name'] = $_POST['user_name'];
    $_SESSION['pass'] = $_POST['user_pass'];
    $_SESSION['master'] = false;

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
    $count = 0;
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

$natural_time =  date(Y) . sprintf("%02s", date(m)) . sprintf("%02s", date(d));

// 年を跨ぐことができない(未実装)

if (!$_SESSION['day_move']) {
    $_SESSION['day_move'] =  date(d);
    $_SESSION['month_move'] = date(m);
    $_SESSION['year_move'] = date(Y);
}

if (!empty($_POST['day_ago'])) {
    $_SESSION['day_move']--;
} else if (!empty($_POST['day_later'])) {
    $_SESSION['day_move']++;
}

if (($_SESSION['day_move']) > date('t', mktime(0, 0, 0, ($_SESSION['month_move'] + 1), 0, $_SESSION['year_move']))) { // 関数の仕様のため+1
    $_SESSION['day_move'] = 1;
    $_SESSION['month_move']++;
} else if (($_SESSION['day_move']) <= 0) {
    $_SESSION['day_move'] = date('t', mktime(0, 0, 0, ($_SESSION['month_move']), 0, $_SESSION['year_move'])); // 関数の仕様のため+なし
    $_SESSION['month_move']--;
}

$year =  date(Y);
$month = sprintf("%02s", $_SESSION['month_move']);
$day = sprintf("%02s", $_SESSION['day_move']);

$one_ago = abs($day) - 1;
$one_later = abs($day) + 1;

if ($one_ago <= 0) {
    $one_ago = date('t', mktime(0, 0, 0, ($_SESSION['month_move']), 0, $_SESSION['year_move']));
} else if ($one_later > date('t', mktime(0, 0, 0, ($_SESSION['month_move'] + 1), 0, $_SESSION['year_move']))) {
    $one_later = 1;
}

$week = ['日', '月', '火', '水', '木', '金', '土'];
$timestamp = mktime(0, 0, 0, $month, $day, $year);
$today_week = $week[date(w, $timestamp)];
$today_youbi = $week[date('w')];

$today = $year . $month . $day;
$output_today = $year . '年' . abs(date(m)) . '月' . abs(date(d)) . '日(' . $today_youbi . ')';
$output_day = $year . '年' . abs($month) . '月' . abs($day) . '日(' . $today_week . ')';

$day_color = false;
if ($natural_time === $today) {
    $day_color = true;
}



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

// スケジュール削除,編集 実装中(削除のみ完成)
if (!empty($_POST['edit_delete'])) {
    $user_sche_data = fopen("./user_data/schedule_data.txt", 'r');
    while ($one_schedule = fgets($user_sche_data)) {
        $user_schedule = explode('$', $one_schedule);
        $user_number = $user_schedule[0];
        $user_day_schedule = $user_schedule[1];

        if ($_SESSION['login_name'][0] === $user_number) {
            $day_schedule = explode('/', $user_day_schedule);
            $schedule_day = $day_schedule[0];
            // $schedule_start_time = $day_schedule[1];
            // $schedule_name = $day_schedule[2];
            // $schedule_end_time = $day_schedule[3];

            if ($today === $schedule_day) {
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
                            //削除
                            $delete_num = $_SESSION['edit_contents'][0] * 3 + 1;
                            for ($i = $delete_num; $i <= $delete_num + 2; $i++) {
                                unset($_day_schedule[$i]);
                            }
                            if (count($_day_schedule) > 1) {
                                $_day_schedule = array_values($_day_schedule);
                                fwrite($overwrite_data, "\n" . $_SESSION["login_name"][0] . '$' . $_day_schedule[0]);
                                unset($_day_schedule[0]);
                                foreach ($_day_schedule as $all_schedule) {
                                    fwrite($overwrite_data, '/' . $all_schedule);
                                }
                                fwrite($overwrite_data, "\n");
                            }
                            
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
}

// todo完了
for ($i = 0; $i <= 30; $i++) {
   if (!empty($_POST["todo_clear_$i"])) {
        $fin_todo_num = $i;

        $user_todo_datas = fopen("./user_data/user_todo.txt", 'r');
        $user_todo_redatas = [];
        while ($one_user_todo = fgets($user_todo_datas)) {
            $one_user_todo = rtrim($one_user_todo, '/');
            $user_todo_data = explode('/', $one_user_todo);
            $user_id = $user_todo_data[0];
            array_pop($user_todo_data);

            if ($user_id === $_SESSION['login_name'][0]) {
                unset($user_todo_data[$fin_todo_num + 1]);
            }
            $user_todo_redatas[] = $user_todo_data;
        }


        $user_todo_redatas = str_replace(array("\r\n","\r","\n"), '', $user_todo_redatas);

        $user_todo_datas = fopen("./user_data/user_todo.txt", 'w');
        foreach ($user_todo_redatas as $user_todo_data) {
            foreach ($user_todo_data as $one_todo) {
                fwrite($user_todo_datas, $one_todo . '/');
            }
            fwrite($user_todo_datas, "\n");
        }

        $login = 'todo';
        
    }
}




// todoリストの作成処理
if (!empty($_POST["todo_box"]) && !empty($_POST["todo_btn"])) {
    $user_todo_datas = fopen("./user_data/user_todo.txt", 'r');
    $user_todo_redatas = [];
    $todo_add = false;
    while ($one_user_todo = fgets($user_todo_datas)) {
        $one_user_todo = rtrim($one_user_todo, '/');
        $user_todo_data = explode('/', $one_user_todo);
        $user_id = $user_todo_data[0];
        array_pop($user_todo_data);

        if ($user_id === $_SESSION['login_name'][0]) {
            array_push($user_todo_data, $_POST["todo_box"]);
            $todo_add = true;

        }

        $user_todo_redatas[] = $user_todo_data;

    }
    if (!$todo_add) {
        $new_todo_user = [];
        $new_todo_user[] = $_SESSION['login_name'][0];
        $new_todo_user[] = $_POST["todo_box"];

        $user_todo_redatas[] = $new_todo_user;
    }

    $user_todo_redatas = str_replace(array("\r\n","\r","\n"), '', $user_todo_redatas);

    $user_todo_datas = fopen("./user_data/user_todo.txt", 'w');
    // var_dump($user_todo_redatas);
    foreach ($user_todo_redatas as $user_todo_data) {
        foreach ($user_todo_data as $one_todo) {
            fwrite($user_todo_datas, $one_todo . '/');
        }
        fwrite($user_todo_datas, "\n");
    }

    $login = 'todo';
}


$_sche_datas = file("./user_data/schedule_data.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$_sche_datas = array_values($_sche_datas);
$re_data = fopen("./user_data/schedule_data.txt", 'w');
foreach ($_sche_datas as $sche_data) {
    fwrite($re_data, $sche_data . "\n");
}
fclose($re_data);



if ($user_info[0] === '2') {
    $_SESSION['master'] = true;
}

if ($login === 'Yes') {
    $datas = fopen("./user_data/schedule_data.txt", 'r');
    while($schedule_data = fgets($datas)){
        $users_data = explode("$",$schedule_data);
        $user_num = $users_data[0];
        $schedule_datas = explode("/", $users_data[1]);
        //erorr : 新規でのログイン時,スケジュールが存在しても表示できない
        //解決 : 型が違った
        if ($_SESSION['master']) { //閲覧用
            if ($user_num === '0') {
                if ($today === $schedule_datas[0]) {
                    $today_schedule = $schedule_datas;
                    break;
                }
            }
        }
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
    $edit_count = 0;
    for ($i = 1; $i <= $schedule_count; $i += 3){
        $time = $today_schedule[$i - 1];
        $action = $today_schedule[$i];
        $end_time = $today_schedule[$i + 1];
        $one_schedule = [$time, $action, $end_time];

        $output_schedules[] = $one_schedule;


        if (!empty($_POST["edit$edit_count"]) && !$_SESSION['master']) {
            $edit_one_schedule = [$edit_count, $time, $action, $end_time];
            $_SESSION['edit_contents'] = $edit_one_schedule;
            $login = 'Edit';
        }
        $edit_count++;
    }
}

if (!empty($_POST['make'])) {
    $login  =  'make';
}

if (!empty($_POST['todo'])) {
    $login  =  'todo';
}


if ($login === 'todo') {
    $users_todo = false;
    $user_todo_datas = fopen("./user_data/user_todo.txt", 'r');
    while ($one_user_todo = fgets($user_todo_datas)) {
        $one_user_todo = rtrim($one_user_todo, '/');
        $user_todo_data = explode('/', $one_user_todo);
        $user_id = $user_todo_data[0];
        array_pop($user_todo_data);

        if ($user_id === $_SESSION['login_name'][0]) {
            $user_todo_output = $user_todo_data;
            array_shift($user_todo_output);
            $users_todo = true;
        }
    }
}


// erorr : このif文が新規登録できなくしている
// if (empty($_SESSION['login_name'])) {
//     $login = 'No';
// }

// echo $login;


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
                <?php if(!$_SESSION['master']): ?>
                    <?php if ($login === 'Yes'): ?>
                        <input type="submit" class="make" name="make" value="スケジュール作成">
                        <input type="submit" class="todo" name="todo" value="To Do リスト">
                        <input type="submit" class="log_out" name="log_out" value="ログアウト">
                    <?php endif ?>
                    <?php if ($login === 'make' || $login === 'Edit'): ?>
                        <input type="submit" class="make" name="return_home" value="戻る">
                        <input type="submit" class="log_out" name="log_out" value="ログアウト">
                    <?php endif ?>
                    <?php if ($login === 'todo'): ?>
                        <input type="submit" class="make" name="return_home" value="ホーム">
                        <input type="submit" class="make" name="make" value="スケジュール作成">
                        <input type="submit" class="log_out" name="log_out" value="ログアウト">
                    <?php endif ?>
                <?php elseif ($_SESSION['master']): ?>
                    <?php if ($login === 'Yes'): ?>
                        <input type="submit" class="make" name="eturan" value="閲覧モード">
                        <input type="submit" class="log_out" name="log_out" value="ログアウト">
                    <?php endif ?>
                <?php endif ?>
            </nav>
            <main>
                <div class="fix_message">
                    <?php if ($fix): ?>
                        <p>只今、アップデート中のためエラーがでる可能性があります。</p>
                    <?php endif ?>
                </div>
                <div class="day_time">
                    <?php if ($login === 'Yes'): ?>
                        <input type="submit" class="day_ago" name="day_ago" value="&#9664;<?php echo $one_ago; ?>日">
                    <?php endif ?>
                    <?php if ($login === 'todo'): ?>
                        <h2 class="today">● <?php echo $output_today; ?> ●</h2>
                    <?php else: ?>
                        <h2 class="today">● <?php echo $output_day; ?> ●</h2>
                    <?php endif ?>
                    <?php if ($login === 'Yes'): ?>
                        <input type="submit" class="day_later" name="day_later" value="<?php echo $one_later; ?>日&#9654;">
                    <?php endif ?>
                </div>
                <?php if ($login === 'Yes' && empty($_POST['make'])): ?>
                    <?php foreach($output_schedules as $sche_key => $one_schedule): ?>
                        <div class="sche_output">
                            <div class="one_schedule">
                                <h3 class="first"><?php echo $one_schedule[0]; ?> 〜</h3>
                                <p><?php echo $one_schedule[1]; ?></p>
                                <h3 class="end">〜 <?php echo $one_schedule[2]; ?></h3>
                            </div>
                            <div class="edit">
                                <input type="submit" name="edit<?php echo $sche_key; ?>" value="編集">
                            </div>
                        </div>
                        
                    <?php endforeach ?>
                    <?php if($output === 'No Schedule'): ?>
                        <div class="No_Schedule">
                            <h3>スケジュールが設定されていません</h3>
                            <?php if(!$_SESSION['master']): ?>
                                <input type="submit" class="make_submit" name="make" value="スケジュール作成">
                            <?php endif ?>
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
                <?php elseif($login === 'Edit'): ?>
                    <div class="edit_home">
                        <h3 class="edit_title">編集モード</h3>
                        <div class="edit_one_schedule">
                            <h3 class="first"><?php echo $_SESSION['edit_contents'][1]; ?> 〜</h3>
                            <p><?php echo $_SESSION['edit_contents'][2]; ?></p>
                            <h3 class="end">〜 <?php echo $_SESSION['edit_contents'][3]; ?></h3>
                        </div>
                        <div class="edit_submit">
                            <input type="submit" class="edit_decision" name="edit_decision" value="変更(できない)">
                            <input type="submit" class="edit_delete" name="edit_delete" value="削除">
                        </div>
                    </div>

                <?php elseif ($login === 'todo'): ?>
                    <div class="todo_main">
                        <h2>ToDoリスト</h2>
                        <div class="make_todo">
                            <input type="text" class="todo_name" name="todo_box">
                            <input type="submit" class="todo_decision" name="todo_btn" value="追加">
                        </div>
                        <div class="output_todo">
                            <?php if ($users_todo): ?>
                                <?php foreach ($user_todo_output as $key => $output_todo): ?>
                                    <div class="one_todo">
                                        <div class="output_todo_name">
                                            <p><?php echo $output_todo; ?></p>
                                        </div>
                                        <div class="todo_clear_btn">
                                            <input type="submit" name="todo_clear_<?php echo $key; ?>" value="完了">
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>
                        </div>
                    </div>
                <?php elseif ($login === 'No'): ?>
                    <div class="login">
                        <h3>ログイン&新規登録</h3>
                        <div class="form_textbox">
                            <input type="text" class="user_name" name="user_name" placeholder="ニックネーム">
                            <input type="text" class="user_pass" name="user_pass" placeholder="パスワード">
                        </div>
                        <input type="submit" class="log_new" name="log" value="I N">
                    </div>
                <?php elseif ($login === 'New'): ?>
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