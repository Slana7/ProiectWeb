<div align="center">

  <h1>🏠 REM - Real Estate Management</h1>
  
  <p>
    A comprehensive real estate management platform for property listings, interactive maps, and client communication.
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
   
<h4>
    <a href="http://localhost/REM/">View Demo</a>
  <span> · </span>
    <a href="#getting-started">Documentation</a>
  <span> · </span>
    <a href="#contact">Report Bug</a>
  <span> · </span>
    <a href="#contact">Request Feature</a>
  </h4>
</div>

<br />

<!-- Table of Contents -->
# :notebook_with_decorative_cover: Table of Contents

- [About the Project](#star2-about-the-project)
  * [Screenshots](#camera-screenshots)
  * [Tech Stack](#space_invader-tech-stack)
  * [Features](#dart-features)
  * [Color Reference](#art-color-reference)
  * [Environment Variables](#key-environment-variables)
- [Getting Started](#toolbox-getting-started)
  * [Prerequisites](#bangbang-prerequisites)
  * [Installation](#gear-installation)
  * [Running Tests](#test_tube-running-tests)
  * [Run Locally](#running-run-locally)
  * [Deployment](#triangular_flag_on_post-deployment)
- [Usage](#eyes-usage)
- [Roadmap](#compass-roadmap)
- [Contributing](#wave-contributing)
  * [Code of Conduct](#scroll-code-of-conduct)
- [FAQ](#grey_question-faq)
- [License](#warning-license)
- [Contact](#handshake-contact)
- [Acknowledgements](#gem-acknowledgements)

  

<!-- About the Project -->
## :star2: About the Project

REM (Real Estate Management) is a comprehensive web platform designed for managing property listings with advanced geospatial features. The application provides an intuitive interface for property owners to list their properties, buyers to search and explore listings, and includes sophisticated mapping capabilities with real-time traffic data, pollution levels, nearby amenities, and property price analytics.

Built with PHP and PostgreSQL with PostGIS extension, REM offers a robust foundation for real estate businesses looking to provide their clients with detailed location-based insights and seamless communication tools.


<!-- Screenshots -->
### :camera: Screenshots

<div align="center"> 
  <img src="https://placehold.co/600x400?text=Your+Screenshot+here" alt="screenshot" />
</div>


<!-- TechStack -->
### :space_invader: Tech Stack

<details>
  <summary>Frontend</summary>
  <ul>
    <li><a href="https://developer.mozilla.org/en-US/docs/Web/HTML">HTML5</a></li>
    <li><a href="https://developer.mozilla.org/en-US/docs/Web/CSS">CSS3</a></li>
    <li><a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript">JavaScript ES6+</a></li>
    <li><a href="https://leafletjs.com/">Leaflet.js</a> - Interactive maps</li>
    <li><a href="https://www.chartjs.org/">Chart.js</a> - Data visualization</li>
  </ul>
</details>

<details>
  <summary>Backend</summary>
  <ul>
    <li><a href="https://www.php.net/">PHP 8.0+</a></li>
    <li><a href="https://www.php.net/manual/en/book.pdo.php">PDO</a> - Database abstraction</li>
    <li><a href="https://www.php.net/manual/en/book.session.php">PHP Sessions</a> - Authentication</li>
  </ul>
</details>

<details>
<summary>Database</summary>
  <ul>
    <li><a href="https://www.postgresql.org/">PostgreSQL 14+</a></li>
    <li><a href="https://postgis.net/">PostGIS</a> - Spatial database extension</li>
    <li><a href="https://www.postgresql.org/docs/current/plpgsql.html">PL/pgSQL</a> - Stored procedures</li>
  </ul>
</details>

<details>
<summary>External APIs</summary>
  <ul>
    <li><a href="https://overpass-api.de/">Overpass API</a> - OpenStreetMap data</li>
    <li><a href="https://openweathermap.org/api">OpenWeatherMap API</a> - Weather & pollution data</li>
  </ul>
</details>

<!-- Features -->
### :dart: Features

- **🏠 Property Management**: Add, edit, and remove property listings with detailed information
- **🗺️ Interactive Maps**: Advanced mapping with Leaflet.js showing property locations
- **📊 Real-time Data Layers**: 
  - Traffic conditions
  - Air pollution levels
  - Nearby amenities (shops, parking, etc.)
- **💬 Messaging System**: Built-in chat for property inquiries with file attachments
- **⭐ Favorites System**: Save and manage favorite properties
- **📈 Price Analytics**: Statistical analysis and price comparison charts
- **🔍 Advanced Filtering**: Filter properties by price, facilities, location, and more
- **📱 Responsive Design**: Mobile-friendly interface
- **🛡️ Secure Authentication**: User registration and login system
- **🗄️ Geospatial Queries**: Advanced PostGIS spatial operations
- **🔔 Smart Notifications**: Flagged messages for urgent content
- **📧 Email Validation**: PL/pgSQL triggers for data integrity

<!-- Color Reference -->
### :art: Color Reference

| Color             | Hex                                                                |
| ----------------- | ------------------------------------------------------------------ |
| Primary Blue | ![#42a5f5](https://via.placeholder.com/10/42a5f5?text=+) #42a5f5 |
| Secondary Blue | ![#1e88e5](https://via.placeholder.com/10/1e88e5?text=+) #1e88e5 |
| Sidebar Dark | ![#1e2a38](https://via.placeholder.com/10/1e2a38?text=+) #1e2a38 |
| Background Light | ![#f5f7fa](https://via.placeholder.com/10/f5f7fa?text=+) #f5f7fa |
| Danger Red | ![#f44336](https://via.placeholder.com/10/f44336?text=+) #f44336 |
| Success Green | ![#43a047](https://via.placeholder.com/10/43a047?text=+) #43a047 |


<!-- Env Variables -->
### :key: Environment Variables

To run this project, you will need to add the following environment variables to your `.env` file

```env
# Application Settings
BASE_URL=/REM/
APP_NAME=REM
DEBUG_MODE=false

# Database Configuration
DB_HOST=localhost
DB_PORT=5432
DB_NAME=REM
DB_USER=postgres
DB_PASS=your_password
```

<!-- Getting Started -->
## 	:toolbox: Getting Started

<!-- Prerequisites -->
### :bangbang: Prerequisites

This project requires the following software to be installed:

- **PHP 8.0+** with extensions:
  ```bash
  # Required PHP extensions
  php-pdo
  php-pgsql
  php-gd
  php-curl
  php-mbstring
  ```

- **PostgreSQL 14+** with PostGIS extension:
  ```sql
  CREATE EXTENSION IF NOT EXISTS postgis;
  ```

- **Web Server** (Apache/Nginx) or PHP built-in server
- **Composer** (optional, for dependency management)

<!-- Installation -->
### :gear: Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Slana7/ProiectWeb.git
   cd ProiectWeb
   ```

2. **Set up the database**
   ```bash
   # Create PostgreSQL database
   createdb REM
   
   # Enable PostGIS extension
   psql -d REM -c "CREATE EXTENSION IF NOT EXISTS postgis;"
   
   # Import schema
   psql -d REM -f sql/schema.sql
   ```

3. **Configure environment**
   ```bash
   # Copy environment file
   cp .env.example .env
   
   # Edit .env with your database credentials
   nano .env
   ```

4. **Set up web server**
   ```bash
   # For development with PHP built-in server
   php -S localhost:8000
   
   # Or configure Apache/Nginx to point to the project root
   ```
   
<!-- Running Tests -->
### :test_tube: Running Tests

To test the PL/pgSQL functions and database constraints, run:

```bash
php test_exceptions.php
```

This will test:
- Email validation triggers
- Price and area constraints
- Database exceptions handling
- Custom PL/pgSQL functions

<!-- Run Locally -->
### :running: Run Locally

1. **Clone the project**
   ```bash
   git clone https://github.com/Slana7/ProiectWeb.git
   ```

2. **Go to the project directory**
   ```bash
   cd ProiectWeb
   ```

3. **Set up environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Set up the database**
   ```bash
   psql -U postgres -c "CREATE DATABASE REM;"
   psql -U postgres -d REM -c "CREATE EXTENSION IF NOT EXISTS postgis;"
   psql -U postgres -d REM -f sql/schema.sql
   ```

5. **Start the development server**
   ```bash   # Using PHP built-in server
   php -S localhost:8000
   
   # Or use XAMPP/WAMP and navigate to http://localhost/REM/
   # Make sure Apache and PostgreSQL services are running
   ```

6. **Access the application**
   ```
   Open your browser and go to http://localhost:8000
   ```


<!-- Deployment -->
### :triangular_flag_on_post: Deployment

For production deployment:

1. **Configure web server** (Apache/Nginx)
2. **Set up SSL certificate** for HTTPS
3. **Configure environment variables** for production
4. **Set up database backups**
5. **Configure proper file permissions**

```bash
# Example Apache virtual host configuration
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/REM
    DirectoryIndex index.php
    
    <Directory /path/to/REM>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```


<!-- Usage -->
## :eyes: Usage

### **For Property Owners:**
1. **Register/Login** to your account
2. **Add Properties** with detailed information and location
3. **Manage Listings** - edit or remove properties
4. **Communicate** with potential buyers via built-in messaging

### **For Property Seekers:**
1. **Browse Properties** on the interactive map
2. **Filter Results** by price, facilities, and location
3. **Save Favorites** for easy access
4. **Contact Owners** directly through the platform
5. **Analyze Prices** with built-in analytics

### **Key Features Demo:**

```php
// Example: Adding a new property with geospatial data
$propertyData = [
    'title' => 'Modern Apartment',
    'price' => 150000,
    'area' => 75,
    'lat' => 47.1585,
    'lng' => 27.6014,
    'facilities' => ['parking', 'air conditioning', 'wifi']
];

$result = addProperty($propertyData);
```

### **Map Integration:**
- **Layer Controls**: Toggle traffic, pollution, shops, and parking data
- **Property Clustering**: Efficient display of multiple properties
- **Real-time Data**: Live traffic and environmental information

<!-- Roadmap -->
## :compass: Roadmap

* [x] ✅ **Core Property Management**
  - [x] Property CRUD operations
  - [x] User authentication system
  - [x] File upload functionality

* [x] ✅ **Geospatial Features**
  - [x] PostGIS integration
  - [x] Interactive maps with Leaflet.js
  - [x] Real-time data layers (traffic, pollution)

* [x] ✅ **Communication System**
  - [x] Built-in messaging
  - [x] File attachments
  - [x] Message flagging system

* [ ] 🚧 **Planned Features**
  - [ ] Email notifications
  - [ ] Advanced property analytics
  - [ ] Mobile app (React Native)
  - [ ] Property viewing scheduler
  - [ ] Payment integration
  - [ ] Multi-language support
  - [ ] API for third-party integrations


<!-- Contributing -->
## :wave: Contributing

<a href="https://github.com/Louis3797/awesome-readme-template/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=Louis3797/awesome-readme-template" />
</a>


Contributions are always welcome!

See `contributing.md` for ways to get started.


<!-- Code of Conduct -->
### :scroll: Code of Conduct

Please read the [Code of Conduct](https://github.com/Louis3797/awesome-readme-template/blob/master/CODE_OF_CONDUCT.md)

<!-- FAQ -->
## :grey_question: FAQ

- **Q: Do I need to install PostGIS separately?**
  + A: Yes, PostGIS is required as a PostgreSQL extension for spatial data operations. Install it with `CREATE EXTENSION IF NOT EXISTS postgis;`

- **Q: Can I use this with MySQL instead of PostgreSQL?**
  + A: No, the application heavily relies on PostGIS spatial functions that are specific to PostgreSQL.

- **Q: How do I configure the map API keys?**
  + A: The application uses free OpenStreetMap data via Overpass API, so no API keys are required for basic functionality.

- **Q: Is the messaging system real-time?**
  + A: Currently, messages are displayed on page refresh. Real-time updates via WebSockets are planned for future releases.

- **Q: How do I backup the spatial data?**
  + A: Use `pg_dump` with the `-Fc` flag to include PostGIS spatial data: `pg_dump -Fc REM > backup.dump`


<!-- License -->
## :warning: License

Distributed under the MIT License. See `LICENSE.txt` for more information.

This project is open source and available under the [MIT License](LICENSE).

## :handshake: Contact

**Project Developer** - Real Estate Management System

📧 Email: your.email@domain.com  
🐙 GitHub: [@your-username](https://github.com/your-username)  
🌐 Project Link: [https://github.com/your-username/REM-Real-Estate-Management](https://github.com/your-username/REM-Real-Estate-Management)

**Local Development:**
- 💻 Local URL: [http://localhost/REM/](http://localhost/REM/)
- 🗄️ Database: PostgreSQL with PostGIS extension
- 🧪 Test Suite: `php test_exceptions.php`

**For Support:**
- 🐛 Bug Reports: [Create an Issue](https://github.com/your-username/REM-Real-Estate-Management/issues)
- 💡 Feature Requests: [Start a Discussion](https://github.com/your-username/REM-Real-Estate-Management/discussions)
- 📖 Documentation: [Wiki](https://github.com/your-username/REM-Real-Estate-Management/wiki)


<!-- Acknowledgments -->
## :gem: Acknowledgements

Special thanks to the following resources and libraries that made this project possible:

### **Core Technologies:**
- [PHP](https://www.php.net/) - Server-side scripting language
- [PostgreSQL](https://www.postgresql.org/) - Advanced open source database
- [PostGIS](https://postgis.net/) - Spatial database extension

### **Frontend Libraries:**
- [Leaflet.js](https://leafletjs.com/) - Mobile-friendly interactive maps
- [Chart.js](https://www.chartjs.org/) - Beautiful charts and graphs
- [Font Awesome](https://fontawesome.com/) - Icons and symbols

### **External APIs & Data:**
- [OpenStreetMap](https://www.openstreetmap.org/) - Free geographic data
- [Overpass API](https://overpass-api.de/) - OSM data extraction
- [OpenWeatherMap](https://openweathermap.org/) - Weather and pollution data

### **Development Resources:**
- [MDN Web Docs](https://developer.mozilla.org/) - Web development reference
- [PostgreSQL Documentation](https://www.postgresql.org/docs/) - Database reference
- [PostGIS Documentation](https://postgis.net/docs/) - Spatial functions reference

### **Design & UX:**
- [Google Fonts](https://fonts.google.com/) - Web typography
- [Unsplash](https://unsplash.com/) - High-quality images
- [Color Hunt](https://colorhunt.co/) - Color palette inspiration

### **Community & Learning:**
- [Stack Overflow](https://stackoverflow.com/) - Developer community
- [GitHub](https://github.com/) - Code hosting and collaboration
- [MDN Learning](https://developer.mozilla.org/en-US/docs/Learn) - Web development tutorials

---

*Built with ❤️ for the real estate community*
