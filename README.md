# StudeerSamen CMS

Zelf-hostbare website met admin panel voor Stichting StudeerSamen.

## Vereisten
- PHP 7.4 of hoger
- Apache/Nginx webserver
- Schrijfrechten op de `content/` map

## Installatie

### 1. Upload naar hosting
Upload alle bestanden via FTP naar je hosting (bijv. Strato `public_html/`).

### 2. Bestandsrechten instellen
```bash
chmod 755 content/
chmod 644 content/*.json
```

### 3. Wachtwoord instellen (eerste keer)
Ga naar `https://jouwdomein.nl/admin/setup.php` en stel je wachtwoord in.
Het wachtwoord wordt opgeslagen als **bcrypt-hash** — nooit in plaintext.

⚠️ **Verwijder `admin/setup.php` na gebruik!**

### 4. Klaar!
- Website: `https://jouwdomein.nl/`
- Admin: `https://jouwdomein.nl/admin/`

## Beveiliging
- ✅ Wachtwoord opgeslagen als **bcrypt-hash** (cost 12)
- ✅ Login via `password_verify()` — nooit plaintext vergelijking
- ✅ `session_regenerate_id()` bij inloggen
- ✅ Brute-force vertraging (sleep 1s bij fout wachtwoord)
- ✅ `.htaccess` blokkeert directe toegang tot config, JSON en hashbestand
- ✅ HttpOnly + SameSite sessie-cookies
- ⚠️ Zet `session.cookie_secure = 1` in config.php als je HTTPS gebruikt

## Structuur
```
├── index.php          # Publieke homepage
├── config.php         # Inloggegevens (niet publiek toegankelijk dankzij .htaccess)
├── .htaccess          # Beveiliging
├── admin/
│   ├── index.php      # Redirect naar login/dashboard
│   ├── login.php      # Inlogpagina
│   ├── dashboard.php  # Admin dashboard
│   ├── save.php       # AJAX opslag endpoint
│   └── logout.php     # Uitloggen
└── content/           # Alle paginateksten (JSON)
    ├── hero.json
    ├── missie.json
    ├── trainingen.json
    ├── team.json
    └── contact.json
```

## Inhoud aanpassen
1. Ga naar `/admin/` en log in
2. Kies een sectie in de zijbalk
3. Bewerk de teksten
4. Klik **Opslaan**

Wijzigingen zijn direct zichtbaar op de website.

## Nginx (alternatief voor Apache)
Als je Nginx gebruikt, voeg dit toe aan je server config:
```nginx
location ~ /config\.php { deny all; }
location ~ /content/.*\.json$ { deny all; }
```
