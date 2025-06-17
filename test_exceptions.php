<?php
require_once __DIR__ . '/src/db/Database.php';

echo "=== Test for PL/pgSQL Exception Handling ===\n\n";

try {
    $conn = Database::connect();
    echo "Database connection established successfully.\n\n";
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Test 1: Invalid email validation (PL/pgSQL trigger)\n";
echo "------------------------------------------------------\n";
try {
    $stmt = $conn->prepare("INSERT INTO users (email, password, name) VALUES (:email, :password, :name)");
    $stmt->execute([
        'email' => 'email-invalid-format',
        'password' => 'test123',
        'name' => 'Test User Invalid'
    ]);
    echo " ERROR: User with invalid email was created (should not happen).\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Invalid email format') !== false) {
        echo "CORRECT: PL/pgSQL exception for invalid email caught successfully.\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n";
    } else {
        echo "ERROR: Different PDO exception caught: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo " ERROR: General exception caught instead of PDOException: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Test 2: Check constraint for negative price\n";
echo "----------------------------------------------\n";
try {
    $stmt = $conn->prepare("INSERT INTO properties (user_id, title, price, area, status) VALUES (1, 'Test Property', -1000, 50, 'for_sale')");
    $stmt->execute();
    echo "ERROR: Property with negative price was created (should not happen).\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'check constraint') !== false || strpos($e->getMessage(), 'price > 0') !== false) {
        echo "CORRECT: Check constraint exception (price > 0) caught successfully.\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n";
    } else {
        echo "ERROR: Different PDO exception caught: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: General exception caught instead of PDOException: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Test 3: Check constraint for negative area\n";
echo "-----------------------------------------------\n";
try {
    $stmt = $conn->prepare("INSERT INTO properties (user_id, title, price, area, status) VALUES (1, 'Test Property', 1000, -50, 'for_sale')");
    $stmt->execute();
    echo "ERROR: Property with negative area was created (should not happen).\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'check constraint') !== false || strpos($e->getMessage(), 'area > 0') !== false) {
        echo "CORRECT: Check constraint exception (area > 0) caught successfully.\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n";
    } else {
        echo "ERROR: Different PDO exception caught: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: General exception caught instead of PDOException: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Test 4: Check constraint for invalid status\n";
echo "------------------------------------------------\n";
try {
    $stmt = $conn->prepare("INSERT INTO properties (user_id, title, price, area, status) VALUES (1, 'Test Property', 1000, 50, 'invalid_status')");
    $stmt->execute();
    echo "ERROR: Property with invalid status was created (should not happen).\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'check constraint') !== false || strpos($e->getMessage(), 'status') !== false) {
        echo "CORRECT: Check constraint exception (valid status) caught successfully.\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n";
    } else {
        echo "ERROR: Different PDO exception caught: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: General exception caught instead of PDOException: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Test 5: Function suggest_price (no exceptions)\n";
echo "-----------------------------------------------\n";
try {
    $stmt = $conn->prepare("SELECT suggest_price(:area, :status)");
    $stmt->execute([
        'area' => 100,
        'status' => 'for_sale'
    ]);
    $result = $stmt->fetchColumn();
    echo "CORRECT: Function suggest_price executed successfully.\n";
    echo "  Suggested price for 100 sqm, for_sale: " . number_format($result, 2) . "\n";
} catch (PDOException $e) {
    echo "ERROR: Unexpected PDO exception in suggest_price call: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "ERROR: Unexpected general exception in suggest_price call: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Test 6: Foreign key constraint exception\n";
echo "-----------------------------------------\n";
try {
    $stmt = $conn->prepare("INSERT INTO properties (user_id, title, price, area, status) VALUES (99999, 'Test Property', 1000, 50, 'for_sale')");
    $stmt->execute();
    echo "ERROR: Property with non-existent user_id was created (should not happen).\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'foreign key constraint') !== false || strpos($e->getMessage(), 'violates') !== false) {
        echo "CORRECT: Foreign key constraint exception caught successfully.\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  Error code: " . $e->getCode() . "\n";
    } else {
        echo "ERROR: Different PDO exception caught: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: General exception caught instead of PDOException: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== Test completed ===\n";
?>
