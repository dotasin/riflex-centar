<?php
header('Content-Type: application/json; charset=UTF-8');

// ============================================
// KONFIGURACIJA
// ============================================

// Test mode - za lokalno testiranje (postavite na true samo za testiranje)
$TEST_MODE = false;

// Email adresa na koju se šalje poruka
$EmailTo = "ivan.tasin@gmail.com";

// ============================================
// METODA SLANJA EMAILA
// ============================================
// Opcije: 'mail', 'smtp', 'sendgrid', 'mailgun'
$EMAIL_METHOD = 'sendgrid'; // Koristi SendGrid za pouzdano slanje emaila

// ============================================
// OPCIJA 1: PHP mail() funkcija (najjednostavnije)
// ============================================
// Koristi se ako je $EMAIL_METHOD = 'mail'
// Ne zahteva dodatnu konfiguraciju, ali može ne raditi na nekim serverima

// ============================================
// OPCIJA 2: Hosting provajderov SMTP (PREPORUČENO)
// ============================================
// Kontaktiraj hosting provajdera za SMTP podatke
// Obično su u formatu: mail.tvojadomena.rs ili smtp.tvojadomena.rs
$USE_SMTP = false; // Postavi na true ako koristiš hosting provajderov SMTP
$SMTP_HOST = 'mail.riflexcentar.rs'; // Ili smtp.tvojadomena.rs - pitaj hosting provajdera
$SMTP_PORT = 587; // Ili 465 za SSL
$SMTP_USER = 'noreply@riflexcentar.rs'; // Email adresa na tvom domenu
$SMTP_PASS = 'LOZINKA_ZA_EMAIL'; // Lozinka za email nalog
$SMTP_SECURE = 'tls'; // 'tls' ili 'ssl'

// ============================================
// OPCIJA 3: SendGrid API (BESPLATNO do 100 emaila/dan) ✅ AKTIVNO
// ============================================
// 
// INSTRUKCIJE:
// 1. Registruj se na: https://sendgrid.com (besplatno)
// 2. U dashboard-u, idi na: Settings → API Keys
// 3. Klikni "Create API Key"
// 4. Ime: "Riflex Website"
// 5. Permissions: "Full Access" (ili samo "Mail Send")
// 6. Klikni "Create & View"
// 7. KOPIRAJ API KEY i unesi ga ispod (prikazuje se samo jednom!)
//
$SENDGRID_API_KEY = ''; // 👇 UNESI SVOJ SENDGRID API KEY OVDE 👇
// Primer: 'SG.abc123def456ghi789jkl012mno345pqr678stu901vwx234yz'

// ============================================
// OPCIJA 4: Mailgun API (BESPLATNO do 5000 emaila/mesec)
// ============================================
// Registruj se na: https://www.mailgun.com (besplatno)
$MAILGUN_API_KEY = ''; // Tvoj Mailgun API Key
$MAILGUN_DOMAIN = ''; // Tvoj Mailgun domain (npr: mg.riflexcentar.rs)

// ============================================
// OBRADA PODATAKA
// ============================================

$EmailFrom = isset($_POST['email']) ? trim(stripslashes($_POST['email'])) : '';
$Subject = isset($_POST['subject']) ? trim(stripslashes($_POST['subject'])) : 'Poruka sa sajta Riflex Centar';
$Name = isset($_POST['author']) ? trim(stripslashes($_POST['author'])) : '';
$Email = isset($_POST['email']) ? trim(stripslashes($_POST['email'])) : '';
$Phone = isset($_POST['phone']) ? trim(stripslashes($_POST['phone'])) : '';
$Message = isset($_POST['message']) ? trim(stripslashes($_POST['message'])) : '';

// Validation
$validationOK = true;
$errors = array();

if (empty($Name)) {
    $validationOK = false;
    $errors[] = "Ime je obavezno polje.";
}

if (empty($Email)) {
    $validationOK = false;
    $errors[] = "Email je obavezno polje.";
} elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
    $validationOK = false;
    $errors[] = "Email adresa nije validna.";
}

if (empty($Message)) {
    $validationOK = false;
    $errors[] = "Poruka je obavezno polje.";
}

if (!$validationOK) {
    echo json_encode(array('success' => false, 'message' => implode(' ', $errors)));
    exit;
}

// Prepare email body text
$Body = "Nova poruka sa sajta Riflex Centar\n";
$Body .= "=====================================\n\n";
$Body .= "Ime i prezime: " . $Name . "\n";
$Body .= "Email: " . $Email . "\n";
if (!empty($Phone)) {
    $Body .= "Telefon: " . $Phone . "\n";
}
$Body .= "Naslov: " . $Subject . "\n\n";
$Body .= "Poruka:\n";
$Body .= "-------------------------------------\n";
$Body .= $Message . "\n";
$Body .= "=====================================\n";

// ============================================
// SLANJE EMAILA
// ============================================

if ($TEST_MODE) {
    // Test režim - čuva poruku u fajl
    $logFile = __DIR__ . '/contact_log.txt';
    $logEntry = date('Y-m-d H:i:s') . "\n";
    $logEntry .= "=====================================\n";
    $logEntry .= $Body . "\n";
    $logEntry .= "=====================================\n\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode(array(
        'success' => true, 
        'message' => 'Poruka je uspešno primljena! (Test režim - podaci su sačuvani u php/contact_log.txt)'
    ));
} else {
    // Slanje emaila prema izabranoj metodi
    $success = false;
    $errorMessage = '';
    
    switch ($EMAIL_METHOD) {
        case 'sendgrid':
            if (!empty($SENDGRID_API_KEY)) {
                $success = sendEmailViaSendGrid($EmailTo, $Subject, $Body, $Email, $Name);
            } else {
                $errorMessage = 'SendGrid API Key nije konfigurisan.';
            }
            break;
            
        case 'mailgun':
            if (!empty($MAILGUN_API_KEY) && !empty($MAILGUN_DOMAIN)) {
                $success = sendEmailViaMailgun($EmailTo, $Subject, $Body, $Email, $Name);
            } else {
                $errorMessage = 'Mailgun API Key ili Domain nije konfigurisan.';
            }
            break;
            
        case 'smtp':
            if ($USE_SMTP) {
                $success = sendEmailViaSMTP($EmailTo, $Subject, $Body, $Email, $Name);
            } else {
                $errorMessage = 'SMTP nije omogućen. Postavi $USE_SMTP = true.';
            }
            break;
            
        case 'mail':
        default:
            // Slanje preko PHP mail() funkcije
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "From: Riflex Centar <noreply@riflexcentar.rs>\r\n";
            $headers .= "Reply-To: " . $Name . " <" . $Email . ">\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "X-Priority: 3\r\n";

            $success = @mail($EmailTo, $Subject, $Body, $headers);
            
            if (!$success) {
                $error = error_get_last();
                error_log("Mail send failed: " . ($error ? $error['message'] : 'Unknown error'));
                $errorMessage = 'PHP mail() funkcija ne radi. Pokušaj sa SMTP ili email servisom.';
            }
            break;
    }
    
    if ($success) {
        echo json_encode(array('success' => true, 'message' => 'Hvala što ste nas kontaktirali! Vaša poruka je uspešno poslata.'));
    } else {
        echo json_encode(array(
            'success' => false, 
            'message' => !empty($errorMessage) ? $errorMessage : 'Greška pri slanju poruke. Molimo pokušajte kasnije ili nas kontaktirajte direktno na ivan.tasin@gmail.com'
        ));
    }
}

// Funkcija za slanje emaila preko SMTP-a
function sendEmailViaSMTP($to, $subject, $body, $replyTo, $replyToName) {
    global $SMTP_HOST, $SMTP_PORT, $SMTP_USER, $SMTP_PASS, $SMTP_SECURE;
    
    if (empty($SMTP_PASS) || $SMTP_PASS === 'LOZINKA_ZA_EMAIL') {
        error_log('SMTP_PASS nije konfigurisan u contact.php');
        return false;
    }
    
    // Koristi PHPMailer ako je dostupan
    if (file_exists(__DIR__ . '/PHPMailer/PHPMailer.php')) {
        require_once __DIR__ . '/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/SMTP.php';
        require_once __DIR__ . '/PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = $SMTP_USER;
            $mail->Password = $SMTP_PASS;
            $mail->SMTPSecure = ($SMTP_SECURE === 'ssl') ? 
                PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : 
                PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $SMTP_PORT;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom($SMTP_USER, 'Riflex Centar');
            $mail->addAddress($to);
            $mail->addReplyTo($replyTo, $replyToName);
            
            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    } else {
        error_log('PHPMailer nije dostupan. Preuzmi sa: https://github.com/PHPMailer/PHPMailer');
        return false;
    }
}

// Funkcija za slanje emaila preko SendGrid API-ja
function sendEmailViaSendGrid($to, $subject, $body, $replyTo, $replyToName) {
    global $SENDGRID_API_KEY;
    
    // Proveri da li je curl dostupan
    if (!function_exists('curl_init')) {
        error_log('SendGrid Error: cURL nije dostupan na serveru');
        return false;
    }
    
    $url = 'https://api.sendgrid.com/v3/mail/send';
    
    // VAŽNO: SendGrid zahteva da "from" email bude verifikovan u SendGrid dashboard-u
    // Za testiranje možeš koristiti: noreply@sendgridtest.com (SendGrid test email)
    // Za produkciju, verifikuj svoj domen u SendGrid: Settings → Sender Authentication
    $data = array(
        'personalizations' => array(
            array(
                'to' => array(array('email' => $to)),
                'subject' => $subject
            )
        ),
        'from' => array('email' => 'noreply@riflexcentar.rs', 'name' => 'Riflex Centar'),
        'reply_to' => array('email' => $replyTo, 'name' => $replyToName),
        'content' => array(
            array(
                'type' => 'text/plain',
                'value' => $body
            )
        )
    );
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $SENDGRID_API_KEY,
        'Content-Type: application/json'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        $errorMsg = "SendGrid Error: HTTP $httpCode";
        if ($curlError) {
            $errorMsg .= " - cURL Error: $curlError";
        }
        if ($response) {
            $errorMsg .= " - Response: $response";
        }
        error_log($errorMsg);
        return false;
    }
}

// Funkcija za slanje emaila preko Mailgun API-ja
function sendEmailViaMailgun($to, $subject, $body, $replyTo, $replyToName) {
    global $MAILGUN_API_KEY, $MAILGUN_DOMAIN;
    
    $url = "https://api.mailgun.net/v3/{$MAILGUN_DOMAIN}/messages";
    
    $data = array(
        'from' => 'Riflex Centar <noreply@' . $MAILGUN_DOMAIN . '>',
        'to' => $to,
        'subject' => $subject,
        'text' => $body,
        'h:Reply-To' => $replyToName . ' <' . $replyTo . '>'
    );
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $MAILGUN_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return true;
    } else {
        error_log("Mailgun Error: HTTP $httpCode - $response");
        return false;
    }
}
?>
