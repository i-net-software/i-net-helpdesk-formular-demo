<?php

    /* ****************************************************************************************************
        EMAIL EMPFÄNGER KONFIGURIEREN
    **************************************************************************************************** */
    define( "RECEIVER", "mein@helpdesk.local" );










    /* ****************************************************************************************************
        WARNING THIS IS A DEMO FORM AND NOT FIT OR SAVE FOR PRODUCTION ENVIRONMENTS

        The following code is a demo for internal use. Please do not use it as is in a live environment
    **************************************************************************************************** */








    /* ****************************************************************************************************
        Im Folgenden sollten keine weiteren Änderungen nötig sein.
    **************************************************************************************************** */
    error_reporting(E_ALL | E_STRICT);
    if ( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' ) {

        // Definition of static strings used for building the email body
        define( "EOL", "\r\n" );
        define( 'TRENNER',  '------T-' . md5(uniqid(time()*20)));
        define( 'ALTTRENNER',  '------A-' . md5(uniqid(time()*20)));

        $error = array();
        $mailto = "";
        $body = "";
        $subject = "";

        /**
         *  Append a section to the email body that contains a file attachement
         *
         *  @param &$secContent     the section content as reference
         *  @param $type            the file type
         *  @param $file            the uploaded file
         *  @param $fileName        the name ofthe uploaded file
         */
        function add_file_to_message( &$secContent, $type, $file, $fileName = null ){

            $fileID = md5($file) . '@inetsoftware.de';
            
            if ( is_null($fileName) ) {
                $fileName = basename( $fileName );
            }

            $zeiger_auf_datei=fopen($file,"rb");
            $filecontents=fread($zeiger_auf_datei,filesize($file));
            fclose($zeiger_auf_datei);

            $secContent .= EOL;
            $secContent .= "--".TRENNER.EOL;
            $secContent .= "Content-Type: $type; name=\"$fileName\"".EOL;
            $secContent .= "Content-Transfer-Encoding: base64".EOL;
            $secContent .= "Content-ID: <$fileID>".EOL;
            $secContent .= "Content-Disposition: inline; filename=$fileName".EOL;
            $secContent .= EOL;
            $secContent .= chunk_split(base64_encode($filecontents));
            return $fileID;
        }

        /**
         *  Create the resulting email and send it
         *
         *  @param $data            the message to send
         *  @param $subject         the subject line of the email
         *  @param $mailFrom        the sender of the email
         *  @param $alternative     an alternative body
         */
        function send_mail( $data=null, $subject=null, $mailFrom=null, $alternative=null ) {

            if ( is_null($mailFrom) || is_null($data) || is_null($subject) ) { return false; }

            $message = $data;
            $params = "";

            $sendTo = RECEIVER;

            // Prepare the email header
            $params .= "From: {$mailFrom}".EOL;
            $params .= "X-From: {$mailFrom}".EOL;
            $params .= "Reply-To: {$mailFrom}".EOL;
            $params .= "Return-Path: {$mailFrom}".EOL;
            $params .= "X-Mailer: i-net software Website Mailer".EOL; 
            $params .= "MIME-Version: 1.0".EOL;
            $params .= "Content-Type: multipart/alternative; boundary=\"".ALTTRENNER."\";".EOL;

            $secContent = "";

            // Add all attachements to the email
            if ( is_array($_FILES) && isset($_FILES['attachments']) ) {
                foreach( $_FILES['attachments']['error'] as $key => $error ) {
                    if ( $error != 0 ) {
                        continue;
                    }
                    
                    add_file_to_message( $secContent, $_FILES['attachments']['type'][$key], $_FILES['attachments']['tmp_name'][$key], $_FILES['attachments']['name'][$key] );
                }
            }

            // add the additional files from the image folder to the email
            // and replace occurences in the message with a respektive CID
            $path = dirname(__FILE__) . '/images/';
            $verz = opendir($path);
            while ( $file=readdir($verz) ) {

                if ( is_file($path.$file) && array_pop(explode('.', $file))=='jpg' ) {
                    $fileID = add_file_to_message( $message, 'image/jpg', $path.$file);
                    $message = str_replace($file, "cid:$fileID", $message);
                }
            }
            closedir($verz);
            
            $Trenner = TRENNER;
            $AltTrenner = ALTTRENNER;

            // add an alternative text part to the message - which is text/plain
            $finalMessage = <<<OUTPUT

This is a multi-part message in MIME format.
--$AltTrenner
Content-Type: text/plain; charset=utf-8; format=flowed
Content-Transfer-Encoding: 7bit

OUTPUT;

        if ( empty($alternative) ) {
            $finalMessage .= "This is a multi-part message in MIME format";
        } else {
            $finalMessage .= $alternative;
        }

        $finalMessage .= <<<OUTPUT

--$AltTrenner
Content-Type: multipart/related; boundary="$Trenner"; Content-Type=text/html

--$Trenner
Content-Type: text/html; charset=utf-8
Content-Transfer-Encoding: 7bit

$message

$secContent
--$Trenner--

--$AltTrenner--
OUTPUT;


            // Add a "/" to the beginning of the next line to not send the message but have it printed to the console.

/*
            // print the resulting message
            print $finalMessage;
            return true;
/*/
            // Send the resulting message
            if ( @mail($sendTo, $subject, $finalMessage, $params) ) {
                return true;
            }
//*/
            return false;
        }

        // Gather variables from the request and add them to the email body
        foreach( $_REQUEST as $key => $val ) {

            if ( empty( $key ) ) { continue; }
            if ( empty( $val ) ) {
                $error[$key] = "Sie müssen einen Wert eingeben/auswählen.";
            }
            
            if ( is_array($val) ) {
                continue;
            }

            // Handling for specific keys
            switch( $key ) {
                case 'submit': break;
                case 'E-Mail': $mailto = $val; break;
                case 'Betreff': $subject = "Eine neue Anfrage wurde gestellt: " . $val;
                default: $body .= $key . ': ' . $val . "\n";
            }
        }

        if ( empty($mailto) ) { $errors['mailto'] = "Eine Absender Email-Adresse muss gesetzt sein."; }
        if ( empty($subject) ) { $errors['classification'] = "Sie haben kein Thema gewählt."; }

        // In case of errors, they will be returned as json
        if ( !empty( $error ) ) {
            header("HTTP/1.1 500", true);
            print json_encode( array( "errors" => $error ) );
        } else {
            // Otherwise the email will be send
            $body .= EOL . EOL . EOL;
            send_mail( nl2br($body), $subject, $mailto, $body );
        }

        exit;
    }

    // Finally: the HTML Body Part
?>
<!DOCTYPE html>
<!--[if lt IE 9]><html class="ie" lang="en"    dir="ltr"><![endif]-->
<!--[if gte IE 9]><!--><html lang="en"    dir="ltr"><!--<![endif]-->
<head>

    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="en" />
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <title>i-net HelpDesk Test Formular</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <!-- You want it? You got it! Or get a better browser right now! -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-bootstrap/0.5pre/assets/css/bootstrap.min.css" />

    <link rel="stylesheet/less" type="text/css" href="./styles/styles.less"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/less.js/2.7.2/less.min.js"></script>

    <script src="./script.js"></script>
    <!--[if lt IE 9]>
        <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body class="no-js">
    <form id="inetWrapper" class="form-horizontal" enctype="multipart/form-data">
        <h1>HelpDesk Formular</h1>
        <!--input type="hidden" name="MAX_FILE_SIZE" value="3000" /-->
    </form>
    <div id="loading"></div>
    <div id="confirm"></div>
    <div id="submit-error"></div>
</body>
</html>