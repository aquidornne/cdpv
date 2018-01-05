<?php

require('php/vendor/phpmailer/PHPMailerAutoload.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    extract($_POST);

    $from = 'noreply@fusioncomunicacao.com';
    $email = 'aquidornne@gmail.com';

    $result = array();

    $mail = new PHPMailer();

    $mail->IsSMTP();
    $mail->SMTPAuth = TRUE;
    $mail->Host = "mail.fusioncomunicacao.com";
    $mail->Port = 587;
    $mail->Username = $from;
    $mail->Password = "Fusion@321";
    $mail->Sender = $from;

    $mail->IsHTML(TRUE);
    $mail->From = $from;
    $mail->FromName = $name;
    $mail->AddAddress($email);

    if (isset($email_form) AND !empty($email_form)) {
        $mail->addReplyTo($email_form, "Resposta");
    }

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    if($_POST['form_type'] == 'contact'){

        $fields_text =
            "<b>Nome: </b>" . $name . "<br>"
            . "<b>Telefone: </b>" . $phone . "<br>"
            . "<b>E-mail: </b>" . $email_form . "<br>"
            . "<b>Mensagem: </b>" . $message . "<br>"
        ;

        try {
            $mail->Subject = utf8_decode('Contato via site');
            $mail->Body .= utf8_decode($fields_text);
            if ($mail->Send()) {
                Tools::redirect('site' . '?success=1');
            } else {
                Tools::redirect('site' . '?success=0');
            }
        } catch (ErrorException $e) {
            $result = FALSE;
        }
    }

    if ($_POST['form_type'] == 'comment') {

        $data_comment = array(
            'EventComment' => array(
                'article_id'      => $event_id,
                'name'            => $name,
                'email'           => $email_form,
                'comment'         => $comment,
            )
        );

        try {
            if ($system->serviceAddComment($data_comment)) {
                $result = TRUE;
            } else {
                $result = FALSE;
            }
        } catch (ErrorException $e) {
            $result = FALSE;
        }
    }
}