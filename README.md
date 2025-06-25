# PHP Input Validation with JavaScript

A comprehensive form validation system that provides both client-side (JavaScript) and server-side (PHP) validation for UAE-specific input fields.

## Features

### Validated Input Fields
- **Email Address**: Full email validation with format checking
- **Emirates ID**: UAE Emirates ID format validation (15 digits in XXX-YYYY-XXXXXXX-X format)
- **UAE Mobile Number**: Multiple UAE mobile number format support

### Validation Types
- **Client-side validation**: Real-time JavaScript validation as user types
- **Server-side validation**: PHP validation on form submission
- **Visual feedback**: Input fields change color based on validation status
- **Error messages**: Detailed error messages for each validation rule

## Supported UAE Mobile Formats
- `+971XXXXXXXXX` (International format)
- `00971XXXXXXXXX` (International alternative)
- `971XXXXXXXXX` (Country code format)
- `05XXXXXXXX` (Local format)
- `XXXXXXXXXX` (10-digit local format)

## Requirements
- PHP 7.0 or higher
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled

## Installation
1. Clone or download the project files
2. Place files in your web server directory (e.g., htdocs for XAMPP)
3. Open `inputvaldationjs.php` in your web browser

## File Structure
```
phpinputsvalidation/
├── inputvaldationjs.php    # Main validation form
├── inputvalidaiton.php     # Additional validation file
└── README.md              # This file
```

## Usage
1. Open the form in your web browser
2. Fill in the required fields:
   - Email address
   - Emirates ID (format: 784-1234-1234567-1)
   - UAE mobile number
3. Watch real-time validation as you type
4. Submit the form to see server-side validation results

## Validation Rules

### Email Validation
- Required field
- Valid email format
- Maximum 254 characters
- Single @ symbol validation
- Domain format validation

### Emirates ID Validation
- Required field
- Exactly 15 digits
- Format: XXX-YYYY-XXXXXXX-X
- Auto-formatting as user types
- Note: Checksum validation disabled for compatibility

### UAE Mobile Validation
- Required field
- Multiple format support
- UAE-specific number validation
- Format cleaning (removes spaces, dashes, parentheses)

## Security Features
- XSS protection using htmlspecialchars()
- Server-side validation backup
- Input sanitization
- Form submission validation

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Development
The code is structured for easy maintenance and extension:
- Separate validation functions for each field type
- Consistent error handling
- Clean, readable code structure
- Responsive design

## License
Open source - feel free to use and modify as needed. 