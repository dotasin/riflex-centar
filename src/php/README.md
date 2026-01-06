# Kontakt Forma - Instrukcije za slanje emaila

## ⚠️ VAŽNO: Gmail App Password više ne radi!

Google je ukinuo App Passwords za standardne Gmail naloge u Q4 2024. **Ne možeš više koristiti Gmail SMTP sa App Password-om.**

## ✅ Rešenja (od najjednostavnijeg do najboljeg)

---

## Rešenje 1: PHP mail() funkcija (POKUŠAJ PRVO)

Najjednostavnije rešenje - ne zahteva dodatnu konfiguraciju.

### Korak 1: U `contact.php` proveri da je:
```php
$EMAIL_METHOD = 'mail';
```

### Korak 2: Testiraj formu

Ako radi - **gotovo!** ✅

Ako ne radi, probaj sledeće rešenje.

---

## Rešenje 2: Hosting provajderov SMTP (PREPORUČENO)

Tvoj hosting provajder obično nudi SMTP server za slanje emaila.

### Korak 1: Kontaktiraj hosting provajdera

Pitaj ih za:
- SMTP server adresu (npr: `mail.tvojadomena.rs` ili `smtp.tvojadomena.rs`)
- SMTP port (obično 587 ili 465)
- Email adresu na tvom domenu (npr: `noreply@riflexcentar.rs`)
- Lozinku za taj email nalog

### Korak 2: Konfiguriši `contact.php`

```php
$EMAIL_METHOD = 'smtp';
$USE_SMTP = true;
$SMTP_HOST = 'mail.riflexcentar.rs'; // Tvoj SMTP server
$SMTP_PORT = 587; // Ili 465 za SSL
$SMTP_USER = 'noreply@riflexcentar.rs'; // Email na tvom domenu
$SMTP_PASS = 'tvoja_lozinka'; // Lozinka za email
$SMTP_SECURE = 'tls'; // 'tls' ili 'ssl'
```

### Korak 3: Instaliraj PHPMailer (opciono, ali preporučeno)

1. Preuzmi: https://github.com/PHPMailer/PHPMailer/releases
2. Raspakuj u `php/PHPMailer/` folder

Struktura:
```
php/
  ├── contact.php
  └── PHPMailer/
      ├── PHPMailer.php
      ├── SMTP.php
      └── Exception.php
```

### Korak 4: Testiraj

---

## Rešenje 3: SendGrid (BESPLATNO - 100 emaila/dan)

SendGrid je profesionalan email servis sa besplatnim planom.

### Korak 1: Registracija

1. Idi na: https://sendgrid.com
2. Registruj se (besplatno)
3. Verifikuj email adresu

### Korak 2: Kreiraj API Key

1. U SendGrid dashboard-u, idi na **Settings** → **API Keys**
2. Klikni **"Create API Key"**
3. Ime: "Riflex Website"
4. Permissions: **"Full Access"** (ili samo "Mail Send")
5. Klikni **"Create & View"**
6. **KOPIRAJ API KEY** (prikazuje se samo jednom!)

### Korak 3: Konfiguriši `contact.php`

```php
$EMAIL_METHOD = 'sendgrid';
$SENDGRID_API_KEY = 'SG.tvoj_api_key_ovde'; // Tvoj SendGrid API Key
```

### Korak 4: Testiraj

**Gotovo!** Email će sada stizati pouzdano. ✅

---

## Rešenje 4: Mailgun (BESPLATNO - 5000 emaila/mesec)

Mailgun je još jedan odličan email servis.

### Korak 1: Registracija

1. Idi na: https://www.mailgun.com
2. Registruj se (besplatno)
3. Verifikuj email adresu

### Korak 2: Dodaj domen

1. U Mailgun dashboard-u, idi na **Sending** → **Domains**
2. Klikni **"Add New Domain"**
3. Unesi subdomen (npr: `mg.riflexcentar.rs`)
4. Dodaj DNS zapise koje Mailgun traži
5. Sačekaj verifikaciju (može potrajati nekoliko sati)

### Korak 3: Kreiraj API Key

1. Idi na **Settings** → **API Keys**
2. Klikni **"Create API Key"**
3. **KOPIRAJ API KEY**

### Korak 4: Konfiguriši `contact.php`

```php
$EMAIL_METHOD = 'mailgun';
$MAILGUN_API_KEY = 'tvoj_api_key_ovde';
$MAILGUN_DOMAIN = 'mg.riflexcentar.rs'; // Tvoj Mailgun domain
```

### Korak 5: Testiraj

---

## Poređenje rešenja

| Rešenje | Cena | Pouzdanost | Lakoća | Preporuka |
|---------|------|------------|--------|-----------|
| PHP mail() | Besplatno | ⭐⭐ | ⭐⭐⭐⭐⭐ | Probaj prvo |
| Hosting SMTP | Besplatno | ⭐⭐⭐⭐ | ⭐⭐⭐ | Najbolje ako imaš domen |
| SendGrid | Besplatno (100/dan) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Najbolje za početak |
| Mailgun | Besplatno (5000/mesec) | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | Najbolje za veće sajtove |

---

## Test režim

Za lokalno testiranje, u `contact.php` postavi:
```php
$TEST_MODE = true;
```

Tada će se poruke čuvati u `php/contact_log.txt` umesto slanja emaila.

**VAŽNO:** Pre nego što postaviš na server, promeni:
```php
$TEST_MODE = false;
```

---

## Provera grešaka

Ako email ne stiže:

1. Proveri da li je `$TEST_MODE = false`
2. Proveri da li je `$EMAIL_METHOD` ispravno postavljen
3. Proveri error log na serveru
4. Proveri da li su svi podaci ispravno uneseni

---

## Najbrže rešenje (5 minuta)

1. Registruj se na SendGrid (besplatno)
2. Kreiraj API Key
3. U `contact.php` postavi:
   - `$EMAIL_METHOD = 'sendgrid';`
   - `$SENDGRID_API_KEY = 'tvoj_api_key';`
4. Testiraj formu

**To je sve!** Email će sada stizati pouzdano. 📧✅

---

## Pitanja?

- **Hosting provajder ne daje SMTP?** → Koristi SendGrid ili Mailgun
- **Ne želiš da se registruješ negde?** → Kontaktiraj hosting provajdera za pomoć
- **Email stiže u spam?** → Proveri SPF i DKIM zapise (hosting provajder može pomoći)
