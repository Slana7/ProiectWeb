<?php
require_once __DIR__ . '/src/db/Database.php';

echo "=== Adding Custom PL/pgSQL Functions ===\n\n";

try {
    $conn = Database::connect();
    echo "✓ Database connection established successfully.\n\n";
} catch (Exception $e) {
    echo "✗ Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

$updatePriceFunction = "
CREATE OR REPLACE FUNCTION update_property_price(_property_id INT, _new_price NUMERIC)
RETURNS NUMERIC AS \$\$
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
\$\$ LANGUAGE plpgsql;
";

$statsFunction = "
CREATE OR REPLACE FUNCTION get_property_stats(_user_id INT)
RETURNS TABLE(
    total_properties INT,
    avg_price NUMERIC,
    min_price NUMERIC,
    max_price NUMERIC
) AS \$\$
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
\$\$ LANGUAGE plpgsql;
";

try {
    echo "Adding function update_property_price...\n";
    $conn->exec($updatePriceFunction);
    echo "✓ Function update_property_price added successfully.\n\n";
    
    echo "Adding function get_property_stats...\n";
    $conn->exec($statsFunction);    echo "✓ Function get_property_stats added successfully.\n\n";
    
    echo "=== All functions added successfully ===\n";
    
} catch (PDOException $e) {
    echo "✗ Error adding functions: " . $e->getMessage() . "\n";
    exit(1);
}
?>
