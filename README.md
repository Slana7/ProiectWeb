<div align="center">

  <h1>REM - Real Estate Management</h1>
  
  <p>
    O platformă completă pentru gestionarea anunțurilor imobiliare, cu hărți interactive, mesagerie și analiză de prețuri.
  </p>
  
  <!-- Badges -->
  <p>
    <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP" />
    <img src="https://img.shields.io/badge/PostgreSQL-316192?style=flat&logo=postgresql&logoColor=white" alt="PostgreSQL" />
    <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black" alt="JavaScript" />
    <img src="https://img.shields.io/badge/Leaflet-199900?style=flat&logo=leaflet&logoColor=white" alt="Leaflet" />
    <img src="https://img.shields.io/badge/PostGIS-008000?style=flat&logo=postgresql&logoColor=white" alt="PostGIS" />
    <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="license" />
  </p>
</div>

---

## Descriere

REM permite:
- Adăugarea, editarea și ștergerea proprietăților cu detalii și localizare pe hartă
- Căutare și filtrare avansată după preț, facilități, zonă și alte criterii
- Vizualizare proprietăți pe hartă interactivă cu date în timp real (trafic, poluare, facilități din apropiere)
- Sistem de mesagerie între utilizatori pentru întrebări și negocieri
- Salvarea proprietăților favorite
- Analiză statistică și comparații de prețuri
- Autentificare și înregistrare securizată
- Administrare utilizatori și anunțuri (rol admin)
- Notificări și validare email

---

## Tehnologii folosite

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Leaflet.js, Chart.js
- **Backend:** PHP 8+, PDO, sesiuni PHP
- **Bază de date:** PostgreSQL 14+ cu extensia PostGIS, PL/pgSQL
- **API-uri externe:** Overpass API (OpenStreetMap), OpenWeatherMap

---

## Structura proiectului

```
REM/
├── index.php
├── public/
│   ├── assets/         # CSS, JS, imagini
│   └── includes/       # Header, footer, componente reutilizabile
├── src/
│   ├── config/         # Configurări aplicație
│   ├── controllers/    # Logica de business
│   ├── models/         # Modele pentru DB
│   ├── services/       # Servicii suplimentare
│   ├── db/             # Conexiune la baza de date
│   └── utils/          # Funcții utilitare
├── views/
│   └── pages/          # Pagini și șabloane HTML
├── sql/                # Scripturi pentru schema bazei de date
└── uploads/            # Fișiere încărcate de utilizatori
```

---

## Instalare rapidă

1. **Clonează proiectul**
   ```bash
   git clone https://github.com/Slana7/ProiectWeb.git
   cd ProiectWeb
   ```

2. **Creează baza de date și activează PostGIS**
   ```bash
   createdb REM
   psql -d REM -c "CREATE EXTENSION IF NOT EXISTS postgis;"
   psql -d REM -f sql/schema.sql
   ```

3. **Configurează fișierul `.env` cu datele tale**

4. **Pornește serverul**
   ```bash
   php -S localhost:8000
   # sau folosește XAMPP/WAMP și accesează http://localhost/REM/
   ```

---

## Utilizare

- Înregistrează-te sau autentifică-te
- Adaugă și gestionează anunțuri imobiliare
- Caută și filtrează proprietăți pe hartă
- Salvează favorite și contactează proprietarii
- Analizează prețurile și vezi date în timp real pe hartă

---

## Testare

Pentru a testa funcțiile PL/pgSQL și constrângerile bazei de date:
```bash
php test_exceptions.php
```

---

## Roadmap

- [x] Management proprietăți (CRUD)
- [x] Hărți interactive și date geospațiale
- [x] Mesagerie și favorite
- [x] Analiză prețuri și filtrare avansată
- [ ] Notificări pe email
- [ ] Integrare plăți și programare vizionări
- [ ] Aplicație mobilă

---

## Licență

Acest proiect este open-source sub licența MIT.

---

## Contact

GitHub: [REM-Project](https://github.com/REM-Project/REM-Real-Estate-Management)

---

*Proiect academic dezvoltat pentru gestionarea modernă a anunțurilor imobiliare*
