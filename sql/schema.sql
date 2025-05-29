
CREATE EXTENSION IF NOT EXISTS postgis;

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    name TEXT NOT NULL,
    role TEXT DEFAULT 'client'
);

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

CREATE TABLE facilities (
    id SERIAL PRIMARY KEY,
    name TEXT UNIQUE NOT NULL
);

CREATE TABLE property_facility (
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    facility_id INTEGER REFERENCES facilities(id),
    PRIMARY KEY (property_id, facility_id)
);

CREATE OR REPLACE FUNCTION validate_email()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.email !~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$' THEN
        RAISE EXCEPTION 'Invalid email format: %', NEW.email;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validate_email
BEFORE INSERT OR UPDATE ON users
FOR EACH ROW EXECUTE FUNCTION validate_email();

CREATE OR REPLACE FUNCTION set_posted_date()
RETURNS TRIGGER AS $$
BEGIN
    NEW.posted_at := NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_set_posted_date
BEFORE INSERT ON properties
FOR EACH ROW EXECUTE FUNCTION set_posted_date();

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

INSERT INTO users (email, password, name)
SELECT 
    'user' || i || '@example.com',
    'hash12345',
    'User ' || i
FROM generate_series(1, 50) AS s(i);

INSERT INTO facilities (name) VALUES 
('parking'), ('air conditioning'), ('wifi'),
('balcony'), ('central heating'), ('elevator');

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

SELECT suggest_price(70, 'for_sale');


CREATE TABLE saved_properties (
    user_id INT REFERENCES users(id),
    property_id INT REFERENCES properties(id),
    PRIMARY KEY (user_id, property_id)
);

CREATE TABLE interested (
    user_id INT REFERENCES users(id),
    property_id INT REFERENCES properties(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE messages (
    id SERIAL PRIMARY KEY,
    sender_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    property_id INT NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    content TEXT,
    attachment TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
