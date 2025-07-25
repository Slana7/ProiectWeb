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

CREATE OR REPLACE FUNCTION update_property_price(_property_id INT, _new_price NUMERIC)
RETURNS NUMERIC AS $$
DECLARE
    current_price NUMERIC;
    price_difference NUMERIC;
BEGIN
    SELECT price INTO current_price FROM properties WHERE id = _property_id;
    
    IF NOT FOUND THEN
        RAISE EXCEPTION 'Property with ID % does not exist', _property_id;
    END IF;
    
    IF _new_price <= 0 THEN
        RAISE EXCEPTION 'Price must be greater than 0, received: %', _new_price;
    END IF;
    
    price_difference := ABS(_new_price - current_price) / current_price * 100;
    
    IF price_difference > 500 THEN
        RAISE EXCEPTION 'Price change too large (%.2f%%). Maximum allowed is 500%%', price_difference;
    END IF;
    
    UPDATE properties SET price = _new_price WHERE id = _property_id;
    
    RETURN _new_price;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION get_property_stats(_user_id INT)
RETURNS TABLE(
    total_properties INT,
    avg_price NUMERIC,
    min_price NUMERIC,
    max_price NUMERIC
) AS $$
BEGIN
    IF NOT EXISTS(SELECT 1 FROM users WHERE id = _user_id) THEN
        RAISE EXCEPTION 'User with ID % does not exist', _user_id;
    END IF;
    
    RETURN QUERY
    SELECT 
        COUNT(*)::INT as total_properties,
        COALESCE(AVG(p.price), 0) as avg_price,
        COALESCE(MIN(p.price), 0) as min_price,
        COALESCE(MAX(p.price), 0) as max_price
    FROM properties p
    WHERE p.user_id = _user_id;
END;
$$ LANGUAGE plpgsql;
ALTER TABLE messages ADD COLUMN is_flagged BOOLEAN DEFAULT FALSE;
ALTER TABLE messages ADD COLUMN is_read BOOLEAN DEFAULT FALSE;

CREATE OR REPLACE FUNCTION flag_keywords()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.content ~* 'urgent|urgență|important|preț|pret|vizionare|vizitare|vizită|vizita|ieșire|iesire|negociere|anunț|anunt' THEN
        NEW.is_flagged := TRUE;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER trg_flag_keywords
BEFORE INSERT ON messages
FOR EACH ROW EXECUTE FUNCTION flag_keywords();

INSERT INTO users (email, password, name, role) VALUES (
    'admin1@rem.com',
    '2y$10$tFk4rTTV0OYEUHls3XGwL.cr2qu01Bp8bX5VGmkNFMR9e3jVbpR0O',
    'Administrator 1',
    'admin'
);

INSERT INTO users (email, password, name, role) VALUES (
    'admin2@rem.com',
    '2y$10$lSMzyZCUBufUBry/Zo3uLO4335oQvJf0LhmfbdviVhIozSN0PwG/e',
    'Administrator 2',
    'admin'
);
