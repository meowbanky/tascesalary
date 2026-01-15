// lib/config/app_config.dart
class AppConfig {
  // API URLs
  static const String apiBaseUrl = 'https://tascesalary.com.ng/auth_api';
  static const String loginEndpoint = '/api/auth/login.php';
  static const String logoutEndpoint = '/api/auth/logout.php';
  static const String verifyEndpoint = '/api/auth/verify.php';

  // Storage Keys
  static const String authTokenKey = 'auth_token'; // Changed from tokenKey
  static const String userDataKey = 'user_data'; // Changed from userKey

  // Timeouts
  static const int connectionTimeout = 30000; // 30 seconds

  // Debug Settings
  static const bool enableDebugging = true;
  static const bool enableLogging = true;

  // App Settings
  static const String appName = 'TASCE Salary';
  static const String appVersion = '1.0.0';

  // API Response Keys
  static const String successKey = 'success';
  static const String messageKey = 'message';
  static const String tokenResponseKey = 'token'; // Changed from tokenKey
  static const String userResponseKey = 'user'; // Changed from userDataKey

  // Validation
  static const int minPasswordLength = 6;
  static const Duration tokenExpiry = Duration(hours: 24);
}
