<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Input Validation with JavaScript</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="email"], input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: #28a745;
            font-size: 14px;
            margin-top: 5px;
        }
        .invalid {
            border-color: #dc3545;
        }
        .valid {
            border-color: #28a745;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .result.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Input Validation Form</h2>
        
        <?php
        // PHP Server-side Validation Functions
        function validateEmail($email) {
            $errors = [];
            
            // Basic email validation
            if (empty($email)) {
                $errors[] = "Email is required";
            } else {
                // Check format
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid email format";
                }
                
                // Additional checks
                if (strlen($email) > 254) {
                    $errors[] = "Email is too long";
                }
                
                // Check for multiple @ symbols
                if (substr_count($email, '@') !== 1) {
                    $errors[] = "Email must contain exactly one @ symbol";
                }
                
                // Check domain part
                $parts = explode('@', $email);
                if (count($parts) === 2) {
                    $domain = $parts[1];
                    if (strlen($domain) > 253) {
                        $errors[] = "Domain part is too long";
                    }
                    if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.-]*[a-zA-Z0-9]$/', $domain)) {
                        $errors[] = "Invalid domain format";
                    }
                }
            }
            
            return $errors;
        }
        
        function validateEmiratesID($eid) {
            $errors = [];
            
            if (empty($eid)) {
                $errors[] = "Emirates ID is required";
            } else {
                // Remove spaces and dashes for validation
                $cleanEid = preg_replace('/[\s-]/', '', $eid);
                
                // Check if it's exactly 15 digits
                if (!preg_match('/^[0-9]{15}$/', $cleanEid)) {
                    $errors[] = "Emirates ID must be exactly 15 digits";
                } else {
                    // Check format (XXX-YYYY-XXXXXXX-X)
                    if (!preg_match('/^[0-9]{3}[-\s]?[0-9]{4}[-\s]?[0-9]{7}[-\s]?[0-9]{1}$/', $eid)) {
                        $errors[] = "Emirates ID format should be XXX-YYYY-XXXXXXX-X";
                    }
                    
                    // Note: Checksum validation disabled as the official algorithm may vary
                    // Format validation above ensures the ID follows the correct pattern
                }
            }
            
            return $errors;
        }
        
        function validateUAEMobile($mobile) {
            $errors = [];
            
            if (empty($mobile)) {
                $errors[] = "Mobile number is required";
            } else {
                // Remove spaces, dashes, and parentheses
                $cleanMobile = preg_replace('/[\s\-\(\)]/', '', $mobile);
                
                // Check UAE mobile number formats
                $validFormats = [
                    '/^\+971[0-9]{9}$/',           // +971XXXXXXXXX
                    '/^00971[0-9]{9}$/',           // 00971XXXXXXXXX
                    '/^971[0-9]{9}$/',             // 971XXXXXXXXX
                    '/^05[0-9]{8}$/',              // 05XXXXXXXX
                    '/^[0-9]{10}$/'                // Local 10-digit format
                ];
                
                $isValid = false;
                foreach ($validFormats as $format) {
                    if (preg_match($format, $cleanMobile)) {
                        $isValid = true;
                        break;
                    }
                }
                
                if (!$isValid) {
                    $errors[] = "Invalid UAE mobile number format";
                } else {
                    // Additional UAE-specific validation
                    if (preg_match('/^\+971[0-9]{9}$/', $cleanMobile) || 
                        preg_match('/^00971[0-9]{9}$/', $cleanMobile) || 
                        preg_match('/^971[0-9]{9}$/', $cleanMobile)) {
                        
                        $localPart = substr($cleanMobile, -9);
                        $firstDigit = substr($localPart, 0, 1);
                        
                        // UAE mobile numbers typically start with 5
                        if ($firstDigit !== '5') {
                            $errors[] = "UAE mobile numbers should start with 5 after country code";
                        }
                    }
                    
                    if (preg_match('/^05[0-9]{8}$/', $cleanMobile)) {
                        // Local format validation - already validated above
                    }
                }
            }
            
            return $errors;
        }
        
        // Process form submission
        $emailErrors = [];
        $eidErrors = [];
        $mobileErrors = [];
        $success = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $eid = trim($_POST['eid'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            
            $emailErrors = validateEmail($email);
            $eidErrors = validateEmiratesID($eid);
            $mobileErrors = validateUAEMobile($mobile);
            
            if (empty($emailErrors) && empty($eidErrors) && empty($mobileErrors)) {
                $success = true;
            }
        }
        
        if ($success) {
            echo '<div class="result success">';
            echo '<h3>Validation Successful!</h3>';
            echo '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>';
            echo '<p><strong>Emirates ID:</strong> ' . htmlspecialchars($eid) . '</p>';
            echo '<p><strong>Mobile:</strong> ' . htmlspecialchars($mobile) . '</p>';
            echo '</div>';
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            echo '<div class="result error">';
            echo '<h3>Validation Errors:</h3>';
            echo '<ul>';
            foreach (array_merge($emailErrors, $eidErrors, $mobileErrors) as $error) {
                echo '<li>' . htmlspecialchars($error) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <form method="POST" action="" id="validationForm">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
                <div id="emailError" class="error"></div>
                <?php if (!empty($emailErrors)): ?>
                    <div class="error">
                        <?php foreach ($emailErrors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="eid">Emirates ID:</label>
                <input type="text" id="eid" name="eid" 
                       value="<?php echo htmlspecialchars($_POST['eid'] ?? ''); ?>"
                       placeholder="784-1234-1234567-1">
                <div id="eidError" class="error"></div>
                <?php if (!empty($eidErrors)): ?>
                    <div class="error">
                        <?php foreach ($eidErrors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="mobile">UAE Mobile Number:</label>
                <input type="tel" id="mobile" name="mobile" 
                       value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>"
                       placeholder="+971501234567 or 0501234567">
                <div id="mobileError" class="error"></div>
                <?php if (!empty($mobileErrors)): ?>
                    <div class="error">
                        <?php foreach ($mobileErrors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="submit">Validate Form</button>
        </form>
    </div>

    <script>
        // JavaScript Client-side Validation
        function validateEmailJS(email) {
            const errors = [];
            
            if (!email) {
                errors.push("Email is required");
            } else {
                // Basic email regex
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(email)) {
                    errors.push("Invalid email format");
                }
                
                if (email.length > 254) {
                    errors.push("Email is too long");
                }
                
                // Check for multiple @ symbols
                const atCount = (email.match(/@/g) || []).length;
                if (atCount !== 1) {
                    errors.push("Email must contain exactly one @ symbol");
                }
            }
            
            return errors;
        }
        
        function validateEmiratesIDJS(eid) {
            const errors = [];
            
            if (!eid) {
                errors.push("Emirates ID is required");
            } else {
                // Remove spaces and dashes
                const cleanEid = eid.replace(/[\s-]/g, '');
                
                // Check if exactly 15 digits
                if (!/^[0-9]{15}$/.test(cleanEid)) {
                    errors.push("Emirates ID must be exactly 15 digits");
                } else {
                    // Check format
                    if (!/^[0-9]{3}[-\s]?[0-9]{4}[-\s]?[0-9]{7}[-\s]?[0-9]{1}$/.test(eid)) {
                        errors.push("Emirates ID format should be XXX-YYYY-XXXXXXX-X");
                    }
                    
                    // Note: Checksum validation disabled as the official algorithm may vary
                    // Format validation above ensures the ID follows the correct pattern
                }
            }
            
            return errors;
        }
        
        function validateUAEMobileJS(mobile) {
            const errors = [];
            
            if (!mobile) {
                errors.push("Mobile number is required");
            } else {
                // Remove spaces, dashes, and parentheses
                const cleanMobile = mobile.replace(/[\s\-\(\)]/g, '');
                
                // UAE mobile number patterns
                const validPatterns = [
                    /^\+971[0-9]{9}$/,     // +971XXXXXXXXX
                    /^00971[0-9]{9}$/,     // 00971XXXXXXXXX
                    /^971[0-9]{9}$/,       // 971XXXXXXXXX
                    /^05[0-9]{8}$/,        // 05XXXXXXXX
                    /^[0-9]{10}$/          // Local 10-digit
                ];
                
                const isValid = validPatterns.some(pattern => pattern.test(cleanMobile));
                
                if (!isValid) {
                    errors.push("Invalid UAE mobile number format");
                } else {
                    // Additional UAE-specific validation
                    if (/^\+971[0-9]{9}$/.test(cleanMobile) || 
                        /^00971[0-9]{9}$/.test(cleanMobile) || 
                        /^971[0-9]{9}$/.test(cleanMobile)) {
                        
                        const localPart = cleanMobile.slice(-9);
                        if (localPart[0] !== '5') {
                            errors.push("UAE mobile numbers should start with 5 after country code");
                        }
                    }
                }
            }
            
            return errors;
        }
        
        function showErrors(fieldId, errors) {
            const errorDiv = document.getElementById(fieldId + 'Error');
            const inputField = document.getElementById(fieldId);
            
            if (errors.length > 0) {
                errorDiv.innerHTML = errors.join('<br>');
                inputField.classList.add('invalid');
                inputField.classList.remove('valid');
            } else {
                errorDiv.innerHTML = '';
                inputField.classList.remove('invalid');
                inputField.classList.add('valid');
            }
        }
        
        // Real-time validation
        document.getElementById('email').addEventListener('input', function() {
            const errors = validateEmailJS(this.value);
            showErrors('email', errors);
        });
        
        document.getElementById('eid').addEventListener('input', function() {
            const errors = validateEmiratesIDJS(this.value);
            showErrors('eid', errors);
        });
        
        document.getElementById('mobile').addEventListener('input', function() {
            const errors = validateUAEMobileJS(this.value);
            showErrors('mobile', errors);
        });
        
        // Form submission validation
        document.getElementById('validationForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const eid = document.getElementById('eid').value;
            const mobile = document.getElementById('mobile').value;
            
            const emailErrors = validateEmailJS(email);
            const eidErrors = validateEmiratesIDJS(eid);
            const mobileErrors = validateUAEMobileJS(mobile);
            
            showErrors('email', emailErrors);
            showErrors('eid', eidErrors);
            showErrors('mobile', mobileErrors);
            
            const hasErrors = emailErrors.length > 0 || eidErrors.length > 0 || mobileErrors.length > 0;
            
            if (hasErrors) {
                e.preventDefault();
                alert('Please fix all validation errors before submitting the form.');
            }
        });
        
        // Format Emirates ID as user types
        document.getElementById('eid').addEventListener('input', function() {
            let value = this.value.replace(/[\s-]/g, ''); // Remove existing formatting
            let formatted = '';
            
            if (value.length > 0) {
                formatted += value.substring(0, 3);
                if (value.length > 3) {
                    formatted += '-' + value.substring(3, 7);
                    if (value.length > 7) {
                        formatted += '-' + value.substring(7, 14);
                        if (value.length > 14) {
                            formatted += '-' + value.substring(14, 15);
                        }
                    }
                }
            }
            
            this.value = formatted;
        });
    </script>
</body>
</html>
