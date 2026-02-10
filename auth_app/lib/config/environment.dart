enum Environment { development, staging, production }

class EnvironmentConfig {
  static const Environment currentEnvironment = Environment.development;

  static String get apiBaseUrl {
    switch (currentEnvironment) {
      case Environment.development:
        return 'https://tascesalary.com.ng/auth_api/api';
      case Environment.staging:
        return 'https://tascesalary.com.ng/auth_api/api';
      case Environment.production:
        return 'https://tascesalary.com.ng/auth_api/api';
    }
  }

  static bool get enableLogging =>
      currentEnvironment == Environment.development;

  static Map<String, String> get headers => {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Environment': currentEnvironment.toString().split('.').last,
      };
}
