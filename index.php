<!DOCTYPE html>
<html>
<head>
	<title>Procesamiento de Correos Gmail</title>
	<meta charset="utf-8"/>
	<meta name="description" content=""/>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php

include("config.php");

if(!$_POST){
    ?>

    <form action="" target="" method="post" name="formDatosCorreo">

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Correo de Gmail"/>

        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Clave de Correo" required/>
        
        <input type="submit" name="enviar" value="Procesar Correos"/>
    </form>

    <?php    
}else{

    if(empty($_POST['email']) || empty($_POST['password']) ) {
        die("Debe completar los campos Email y Password");
    }
    
    set_time_limit(4000); 
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Conexión Fallida: " . $conn->connect_error);
    }
    
    // Connect to gmail
    $imapPath = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
    $username = $_POST['email'];
    $password = $_POST['password'];
    
    $count = 0;
    
    // try to connect 
    $inbox = imap_open($imapPath,$username,$password) 
    or die('<table id="resultados">
            <tr>
               <th>No se pudo conectar al correo: '.imap_last_error().'</th>
            </tr>
            <td><input type="button" name="volver" value="Volver" onclick="location.href=\'index.php\';"/></td>
            </table>');
    
    // search and get unseen emails, function will return email ids
    $emails = imap_search($inbox,$mailSearchType);

    ?>

    <table id="resultados">
    <tr>
        <th>Comienza Procesamiento de Correos</th>
    </tr>

    <?php
    
    foreach($emails as $mail) {
    
        $headerInfo = imap_headerinfo($inbox,$mail);
    
        $subject = $headerInfo->subject;
        $from = $headerInfo->fromaddress;
        $fecha = $headerInfo->date;
    
        $message = imap_fetchbody($inbox,$mail,1);
    
        $findme = "DevOps";
    
        $pos = strpos($subject.$message, $findme);
    
        if ($pos === false) {

            echo "<tr><td>La cadena '$findme' NO fue encontrada en este correo</td></tr>";

        } else {
            echo "<tr><td>La cadena '$findme' SI fue encontrada en este correo !!</td></tr>";
            
            $count++;
    
            $fechaFormateada = date("Y-m-d", strtotime($fecha));
    
            $sql = "INSERT INTO mails (fecha, from_mail, subject) VALUES ('$fechaFormateada', '$from', '$subject')";
    
            if ($conn->query($sql) === TRUE) {
            echo "<tr><td>Correo Insertado Satisfactoriamente</td></tr>";
            } else {
            echo "<tr><td>Error: " . $sql . "<br>" . $conn->error."</td></tr>";
            }
    
            //$conn->close();
        }
    }

    echo "<tr><td>Se encontraron $count correos con la palabra $findme en el Asunto o el Cuerpo.</td></tr>";

    ?>

    <tr>
        <th>Finaliza Procesamiento de Correos</th>
    </tr>
    <tr>
        <td><input type="button" name="volver" value="Volver" onclick="location.href='index.php'"/></td>
    </tr>
    </table>

    <?php
    
    $conn->close();
    // colse the connection
    imap_expunge($inbox);
    imap_close($inbox);    
}

?>

</body>
</html>