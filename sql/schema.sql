
-- REM - Real Estate Management Database Schema (PostgreSQL + PostGIS)

-- 0. Activare extensii
CREATE EXTENSION IF NOT EXISTS postgis;

-- 1. Tabelul users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT DEFAULT 'client'
);

-- 2. Tabelul properties
CREATE TABLE properties (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    title TEXT NOT NULL,
    description TEXT,
    price NUMERIC NOT NULL CHECK (price > 0),
    area INTEGER NOT NULL CHECK (area > 0),
    location GEOGRAPHY(Point, 4326),
    status TEXT CHECK (status IN ('for_sale', 'for_rent')),
    posted_at TIMESTAMP DEFAULT NOW()
);

-- 3. Tabelul facilities
CREATE TABLE facilities (
    id SERIAL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);

-- 4. Tabelul de legătură property_facility (M:N)
CREATE TABLE property_facility (
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    facility_id INTEGER REFERENCES facilities(id),
    PRIMARY KEY (property_id, facility_id)
);

-- 5. Funcție pentru validarea emailului
CREATE OR REPLACE FUNCTION validate_email()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.email !~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        RAISE EXCEPTION 'Invalid email format: %', NEW.email;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 6. Trigger pentru validare email
CREATE TRIGGER trg_validate_email
BEFORE INSERT OR UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION validate_email();

-- 7. Funcție pentru setarea automată a datei
CREATE OR REPLACE FUNCTION set_posted_date()
RETURNS TRIGGER AS $$
BEGIN
    NEW.posted_at := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 8. Trigger pentru setarea datei
CREATE TRIGGER trg_set_posted_date
BEFORE INSERT ON properties
FOR EACH ROW EXECUTE FUNCTION set_posted_date();

-- 9. Funcție pentru sugestie automată preț
CREATE OR REPLACE FUNCTION suggest_price(_area INT, _status TEXT)
RETURNS NUMERIC AS $$
DECLARE
    avg_price NUMERIC;
BEGIN
    SELECT AVG(price / area) INTO avg_price
    FROM properties
    WHERE status = _status;

    IF avg_price IS NULL THEN
        RETURN 1000 * _area;
    ELSE
        RETURN avg_price * _area;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- 10. Inserare utilizatori
INSERT INTO users (email, password, name)
SELECT 
    'user' || i || '@example.com',
    'hash12345',
    'User ' || i
FROM generate_series(1, 50) AS s(i);

-- 11. Inserare facilități
INSERT INTO facilities (name) VALUES 
('parking'), ('air conditioning'), ('wifi'),
('balcony'), ('central heating'), ('elevator');

-- 12. Inserare proprietăți (pe raza județului Iași)
-- Coordonate aproximative: lat 47.15 - 47.25, lon 27.50 - 27.65
INSERT INTO properties (user_id, title, description, price, area, location, status)
SELECT 
    (RANDOM() * 49 + 1)::INT,
    'Property ' || i,
    'Beautiful property number ' || i,
    (RANDOM() * 100000 + 30000)::INT,
    (RANDOM() * 100 + 30)::INT,
    ST_GeogFromText('SRID=4326;POINT(' || 
        (27.50 + RANDOM() * 0.15) || ' ' || 
        (47.15 + RANDOM() * 0.10) || ')'),
    CASE WHEN RANDOM() < 0.5 THEN 'for_sale' ELSE 'for_rent' END
FROM generate_series(1, 100) AS s(i);

-- 13. Atribuire aleatorie de facilități (1-3 per proprietate)
DO $$
DECLARE
    i INT;
    nr_fac INT;
    f_id INT;
BEGIN
    FOR i IN 1..100 LOOP
        nr_fac := FLOOR(RANDOM() * 3 + 1);
        FOR f_id IN 1..nr_fac LOOP
            INSERT INTO property_facility (property_id, facility_id)
            VALUES (i, (RANDOM() * 5 + 1)::INT)
            ON CONFLICT DO NOTHING;
        END LOOP;
    END LOOP;
END $$;

-- 14. Test sugestie preț
SELECT suggest_price(70, 'for_sale');
