<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メールマガジン送信</title>
</head>

<body>
    <?php

    //会員メール一覧テキスト取得ここから
    $folder = "./data/";
    $file = $folder . 'mailmaga.txt';

    if (!file_exists($file)) {
        echo 'ファイルが存在しません。';
        exit;
    }
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        echo 'ファイルの読み込みに失敗しました。';
        exit;
    }
    //会員メール一覧取得ここまで

    //メール送信処理ここから
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'php_mailer/vendor/autoload.php';

    $mail = new PHPMailer(true);

    //メール配信内容取得
    $sql = "SELECT * From t_mailmaga WHERE id = 1";
    $m = $dbh->prepare($sql);
    $m->execute();
    $rs = $m->fetch(PDO::FETCH_ASSOC);

    $update = $rs["up_date"];

    //署名を取得
    $sql = "SELECT * From t_signature";
    $sign = $dbh->prepare($sql);
    $sign->execute();
    $s = $sign->fetch(PDO::FETCH_ASSOC);
    $signature = $s["naiyo"];
    if (date('Y-m-d H:i:s', strtotime($update . '+1 minute')) < date('Y-m-d H:i:s')) {

        $email = $rs["email"];
        $title = $rs["title"];
        $bodytext = $rs["naiyo"];

        if ($rs["sentnumber"] < $rs["number"]) {
            $a = $rs["sentnumber"];
            //5件ずつ送信
            $b = $a + 5;

            for ($i = $a; $i < $b; $i++) {
                $toname = "";
                $company = "";
                $name = "";
                $useremail = "";
                $user = explode(",", $lines[$i]);
                $company = $user[0];
                $name = trim($user[1]);
                $useremail = $user[2];

                if (!empty($company)) {
                    $toname = $company . " 御中 \n";
                }
                if (!empty($name)) {
                    $toname .= $name . " 様 \n";
                }

                $naiyo = $toname . "\n" . $bodytext . "\n\n" . $signature;

                if (!empty($useremail)) {
                    try {

                        //送信サーバ設定
                        $mail->SMTPDebug = 2;
                        $mail->isSMTP();
                        $mail->Host = '';
                        $mail->SMTPAuth = true;
                        $mail->Username = '';
                        $mail->Password = '';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->CharSet = 'ISO-2022-JP';

                        //受信者設定
                        $mail->setFrom($email, mb_encode_mimeheader('')); //送信者メール
                        $mail->addAddress($useremail); //受信者メール

                        //メール内容設定
                        $mail->isHTML(false);
                        $mail->Subject = mb_encode_mimeheader($title, 'ISO-2022-JP');
                        $mail->Body = mb_convert_encoding($naiyo, 'ISO-2022-JP');
                        $mail->send();
                        echo 'メールが送信されました。';
                    } catch (Exception $e) {
                        echo "メールの送信に失敗しました: {$mail->ErrorInfo}";
                    } //catchここまで
                } //if(!empty($useremail))ここまで
            } //for文ここまで

            if ($b >= $rs["number"]) {
                $b = $rs["number"];
            }

            $sql = "UPDATE t_mailmaga SET sentnumber = $b , up_date = NOW() WHERE id = 1";
            $stm = $dbh->prepare($sql);
            $stm->execute();
        } else {
            echo "完了しました。";
        } // if文ここまで
    } else {
        echo "1分に1回の送信制限をかけています。<br>";
        echo "次の送信は" . date('Y-m-d H:i:s', strtotime($update . '+1 minute')) . "です。";
    }
    ?>

</body>

</html>