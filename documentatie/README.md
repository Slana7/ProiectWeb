<div align="center">

  <h1>REM - Real Estate Management</h1>
  
  <p>
    Platformă web avansată pentru gestionarea anunțurilor imobiliare, cu funcționalități moderne de analiză, cartografiere și comunicare.
  </p>
  
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
## Demo Video

YouTube(https://www.youtube.com/watch?v=EQ9Xip7KBk0&ab_channel=TeodoraNechita)

## Abstract

REM (Real Estate Management) este o aplicație web dezvoltată pentru a facilita gestionarea, analiza și vizualizarea anunțurilor imobiliare. Platforma integrează funcționalități de cartografiere interactivă, analiză statistică, comunicare între utilizatori și administrare avansată, adresând atât nevoile utilizatorilor finali, cât și ale administratorilor.

---

## Table of Contents

- [Descriere generală](#descriere-generală)
- [Funcționalități principale](#funcționalități-principale)
- [Arhitectură și tehnologii](#arhitectură-și-tehnologii)
- [Structura proiectului](#structura-proiectului)
- [Instrucțiuni de instalare](#instrucțiuni-de-instalare)
- [Utilizare](#utilizare)
- [Testare și validare](#testare-și-validare)
- [Roadmap](#roadmap)
- [Licență](#licență)
- [Contact](#contact)
- [Referințe](#referințe)

---

## Descriere generală

REM oferă un cadru complet pentru publicarea, gestionarea și analiza anunțurilor imobiliare, integrând date geospațiale și instrumente de comunicare. Platforma este destinată atât utilizatorilor individuali, cât și agențiilor imobiliare, oferind suport pentru operațiuni CRUD, filtrare avansată, vizualizare pe hartă și analiză de piață.

---

## Funcționalități principale

- **Gestionare proprietăți:** Adăugare, editare, ștergere și vizualizare anunțuri cu detalii extinse și localizare geospațială.
- **Cartografiere interactivă:** Vizualizare proprietăți pe hartă cu Leaflet.js, integrare date externe (trafic, poluare, facilități).
- **Filtrare și căutare avansată:** După preț, facilități, zonă, suprafață, status și alți parametri.
- **Mesagerie internă:** Sistem de comunicare între utilizatori pentru întrebări și negocieri.
- **Favorite:** Salvarea și gestionarea proprietăților preferate.
- **Analiză statistică:** Grafică și comparații de prețuri, evoluții și tendințe.
- **Administrare:** Panou dedicat pentru administratori (gestionare utilizatori, anunțuri, moderare).
- **Securitate:** Autentificare, înregistrare, validare email, sesiuni securizate.
- **Notificări:** Sistem de notificări și validări automate.

---

## Arhitectură și tehnologii

- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Leaflet.js, Chart.js
- **Backend:** PHP 8+, PDO, sesiuni PHP
- **Bază de date:** PostgreSQL 14+ cu extensia PostGIS, PL/pgSQL
- **API-uri externe:** Overpass API (OpenStreetMap), OpenWeatherMap
- **Structură MVC:** Separare clară între modele, controllere și view-uri pentru mentenanță și scalabilitate.

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

## Instrucțiuni de instalare

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

3. **Configurează fișierul `.env` cu datele tale de conectare la baza de date**

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
- Accesează panoul de administrare pentru management avansat

---

## Testare și validare

- Pentru a testa funcțiile PL/pgSQL și constrângerile bazei de date:
  ```bash
  php test_exceptions.php
  ```
- Testare manuală a funcționalităților principale se poate realiza din interfața web, cu utilizatori de test.

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

Email: rem.contact@example.com  
GitHub: [REM-Project](https://github.com/REM-Project/REM-Real-Estate-Management)

---

## Referințe

- [Leaflet.js Documentation](https://leafletjs.com/)
- [PostGIS Documentation](https://postgis.net/documentation/)
- [OpenStreetMap Wiki](https://wiki.openstreetmap.org/)

---

*Proiect academic dezvoltat pentru gestionarea modernă a anunțurilor imobiliare*
