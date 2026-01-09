<?php

require_once("conexion.php");
date_default_timezone_set('Europe/Madrid');


require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = isset($_POST["correo"]) ? trim($_POST["correo"]) : "";

    if (empty($email)) {
        header("Location: recuperar.php?error=El correo es obligatorio");
        exit();
    }

 
    $stmt = $con->prepare("SELECT usuarioID FROM dato WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        
     
        $token = bin2hex(random_bytes(50));

      
        $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));

    
        $update = $con->prepare("UPDATE dato SET token_login = ?, tokenTemporal = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiracion, $email);
        
        if ($update->execute()) {
            
        
            $mail = new PHPMailer(true);

            try {
             
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sumaxalex81@gmail.com'; 
                $mail->Password   = 'pqwpugnkkyvmmazm'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

               
                $mail->setFrom('sumaxalex81@gmail.com', 'Soporte Foro');
                $mail->addAddress($email);

              
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Recuperar tu contraseña';
                
                
                $enlace = "http://localhost/ProyectoJAN-MAX/php/contranueva.php?token_login=".$token;

                $mail->Body    = "
                    <h1>Restablecer Contraseña</h1>
                    <p>Has solicitado cambiar tu contraseña.</p>
                    <p>Haz clic aquí para poner una nueva (el enlace caduca en 1 hora):</p>
                    <p><a href='$enlace'>$enlace</a></p>
                ";

                $mail->send();
                
                header("Location: recuperar.php?msg=Revisa tu correo. Se ha enviado el enlace.");
                exit();

            } catch (Exception $e) {
                header("Location: recuperar.php?error=Error al enviar correo: {$mail->ErrorInfo}");
                exit();
            }
        } else {
            header("Location: recuperar.php?error=Error en base de datos.");
            exit();
        }
    } else {
        header("Location: recuperar.php?msg=Si el correo existe, se ha enviado el enlace.");
        exit();
    }
} else {
    header("Location: recuperar.php");
    exit();
}
?>