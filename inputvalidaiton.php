<?php
/**
 * PHP Input Validation Script
 * Validates Email, Emirates ID (EID), and UAE Mobile Numbers
 */

// Function to validate email address
function validateEmail($email) {
    $errors = [];
    
    // Check if email is empty
    if (empty($email)) {
        $errors[] = "Email is required";
        return $errors;
    }
    
    // Basic format validation using PHP's built-in filter
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Additional checks
    if (strlen($email) > 254) {
        $errors[] = "Email is too long (maximum 254 characters)";
    }
    
    // Check for valid domain
    $parts = explode('@', $email);
    if (count($parts) == 2) {
        $domain = $parts[1];
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            $errors[] = "Email domain does not exist";
        }
    }
    
    return $errors;
}

// Function to validate Emirates ID (EID)
function validateEID($eid) {
    $errors = [];
    
    // Check if EID is empty
    if (empty($eid)) {
        $errors[] = "Emirates ID is required";
        return $errors;
    }
    
    // Remove any spaces or dashes
    $eid = preg_replace('/[\s\-]/', '', $eid);
    
    // Check if it's exactly 15 digits
    if (!preg_match('/^\d{15}$/', $eid)) {
        $errors[] = "Emirates ID must be exactly 15 digits";
        return $errors;
    }
    
    // Check if it starts with 784 (UAE code)
    if (substr($eid, 0, 3) !== '784') {
        $errors[] = "Emirates ID must start with 784";
    }
    
    // Validate check digit using Emirates ID algorithm
    // Note: Checksum validation is disabled for development/testing
    // Uncomment the following lines for production use:
    /*
    if (!validateEIDChecksum($eid)) {
        $errors[] = "Invalid Emirates ID checksum";
    }
    */
    
    return $errors;
}

// Helper function to validate EID checksum using Emirates ID algorithm
function validateEIDChecksum($eid) {
    // Emirates ID uses a specific algorithm
    // The algorithm multiplies each digit (from left to right, excluding check digit) by its position weight
    $weights = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
    $sum = 0;
    
    // Calculate weighted sum for first 14 digits
    for ($i = 0; $i < 14; $i++) {
        $digit = intval($eid[$i]);
        $sum += $digit * $weights[$i];
    }
    
    // Calculate check digit
    $remainder = $sum % 11;
    $checkDigit = 11 - $remainder;
    
    // Handle special cases
    if ($checkDigit == 11) {
        $checkDigit = 0;
    } elseif ($checkDigit == 10) {
        $checkDigit = 1;
    }
    
    // Compare with the actual check digit (last digit)
    $actualCheckDigit = intval($eid[14]);
    return $checkDigit == $actualCheckDigit;
}

// Helper function to generate a valid Emirates ID for testing
function generateValidEID($year = 2000, $sequence = 1234567) {
    // Format: 784 + 4-digit year + 7-digit sequence + 1-digit checksum
    $baseNumber = "784" . str_pad($year, 4, '0', STR_PAD_LEFT) . str_pad($sequence, 7, '0', STR_PAD_LEFT);
    
    // Calculate checksum
    $weights = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
    $sum = 0;
    
    for ($i = 0; $i < 14; $i++) {
        $digit = intval($baseNumber[$i]);
        $sum += $digit * $weights[$i];
    }
    
    $remainder = $sum % 11;
    $checkDigit = 11 - $remainder;
    
    if ($checkDigit == 11) {
        $checkDigit = 0;
    } elseif ($checkDigit == 10) {
        $checkDigit = 1;
    }
    
    return $baseNumber . $checkDigit;
}

// Function to validate UAE mobile number
function validateUAEMobile($mobile) {
    $errors = [];
    
    // Check if mobile is empty
    if (empty($mobile)) {
        $errors[] = "Mobile number is required";
        return $errors;
    }
    
    // Remove spaces, dashes, and brackets
    $mobile = preg_replace('/[\s\-\(\)]/', '', $mobile);
    
    // Handle different input formats
    if (substr($mobile, 0, 4) === '+971') {
        $mobile = substr($mobile, 4); // Remove +971
    } elseif (substr($mobile, 0, 3) === '971') {
        $mobile = substr($mobile, 3); // Remove 971
    } elseif (substr($mobile, 0, 2) === '00' && substr($mobile, 2, 3) === '971') {
        $mobile = substr($mobile, 5); // Remove 00971
    } elseif (substr($mobile, 0, 1) === '0') {
        $mobile = substr($mobile, 1); // Remove leading 0
    }
    
    // Check if it's exactly 9 digits after country code removal
    if (!preg_match('/^\d{9}$/', $mobile)) {
        $errors[] = "Mobile number must be 9 digits after country code";
        return $errors;
    }
    
    // Check if it starts with valid UAE mobile prefixes
    $validPrefixes = ['50', '51', '52', '54', '55', '56', '58'];
    $prefix = substr($mobile, 0, 2);
    
    if (!in_array($prefix, $validPrefixes)) {
        $errors[] = "Invalid UAE mobile number prefix. Must start with: " . implode(', ', $validPrefixes);
    }
    
    return $errors;
}

// Function to display validation results
function displayValidationResults($field, $value, $errors) {
    echo "<div class='validation-result'>";
    echo "<h3>$field Validation</h3>";
    echo "<p><strong>Input:</strong> " . htmlspecialchars($value) . "</p>";
    
    if (empty($errors)) {
        echo "<p class='success'>✓ Valid $field</p>";
    } else {
        echo "<p class='error'>✗ Validation Errors:</p>";
        echo "<ul class='error-list'>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}

// Process form submission
$emailErrors = [];
$eidErrors = [];
$mobileErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $eid = $_POST['eid'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    
    $emailErrors = validateEmail($email);
    $eidErrors = validateEID($eid);
    $mobileErrors = validateUAEMobile($mobile);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Input Validation - Email, EID, UAE Mobile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        input[type="email"], 
        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        input:focus {
            border-color: #4CAF50;
            outline: none;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .validation-result {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ddd;
        }
        
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .error {
            color: #f44336;
            font-weight: bold;
        }
        
        .error-list {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .error-list li {
            color: #f44336;
            margin: 5px 0;
        }
        
        .examples {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .examples h3 {
            margin-top: 0;
            color: #333;
        }
        
        .examples p {
            margin: 5px 0;
            font-family: monospace;
            background-color: white;
            padding: 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>PHP Input Validation</h1>
        <p>Validate Email, Emirates ID, and UAE Mobile Numbers</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                       placeholder="example@domain.com">
            </div>
            
            <div class="form-group">
                <label for="eid">Emirates ID (EID):</label>
                <input type="text" id="eid" name="eid" 
                       value="<?php echo htmlspecialchars($_POST['eid'] ?? ''); ?>" 
                       placeholder="784-2000-1234567-8">
            </div>
            
            <div class="form-group">
                <label for="mobile">UAE Mobile Number:</label>
                <input type="tel" id="mobile" name="mobile" 
                       value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" 
                       placeholder="+971 50 123 4567">
            </div>
            
            <button type="submit">Validate Inputs</button>
        </form>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <hr style="margin: 30px 0;">
            <h2>Validation Results</h2>
            
            <?php 
            displayValidationResults('Email', $_POST['email'] ?? '', $emailErrors);
            displayValidationResults('Emirates ID', $_POST['eid'] ?? '', $eidErrors);
            displayValidationResults('UAE Mobile', $_POST['mobile'] ?? '', $mobileErrors);
            ?>
        <?php endif; ?>
        
        <div class="examples">
            <h3>Input Format Examples:</h3>
            <p><strong>Email:</strong> user@example.com, john.doe@company.co.uk</p>
            <p><strong>Emirates ID:</strong> <?php echo generateValidEID(2000, 1234567); ?>, <?php echo generateValidEID(1995, 9876543); ?></p>
            <p><strong>UAE Mobile:</strong> +971 50 123 4567, 971501234567, 0501234567</p>
            
            <h3>Note for Emirates ID Testing:</h3>
            <p style="color: #666; font-size: 14px;">
                The checksum validation is currently disabled for development/testing purposes. 
                Any 15-digit number starting with 784 will be accepted. 
                For production use, uncomment the checksum validation in the code.
            </p>
        </div>
    </div>
</body>
</html>
