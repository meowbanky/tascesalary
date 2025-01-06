// lib/services/http_service.dart
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:async'; // Add this import for TimeoutException
import '../config/app_config.dart';

class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final int statusCode;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    required this.statusCode,
  });
}

class HttpService {
  static final HttpService _instance = HttpService._internal();
  factory HttpService() => _instance;
  HttpService._internal();

  final Map<String, String> _baseHeaders = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  Future<ApiResponse<T>> post<T>(
    String endpoint, {
    Map<String, dynamic>? body,
    Map<String, String>? additionalHeaders,
    T Function(Map<String, dynamic>)? fromJson,
  }) async {
    try {
      final uri = Uri.parse('${AppConfig.apiBaseUrl}$endpoint');
      final headers = {..._baseHeaders, ...?additionalHeaders};

      if (AppConfig.enableLogging) {
        print('Making request to: $uri');
        print('Headers: $headers');
        print('Body: $body');
      }

      final response = await http
          .post(
        uri,
        headers: headers,
        body: body != null ? json.encode(body) : null,
      )
          .timeout(
        const Duration(milliseconds: AppConfig.connectionTimeout),
        onTimeout: () {
          throw TimeoutException('Request timed out');
        },
      );

      if (AppConfig.enableLogging) {
        print('Response Status Code: ${response.statusCode}');
        print('Response Body: ${response.body}');
      }

      if (response.statusCode == 404) {
        return ApiResponse(
          success: false,
          message: 'API endpoint not found',
          statusCode: response.statusCode,
        );
      }

      try {
        final responseData = json.decode(response.body);

        if (response.statusCode >= 200 && response.statusCode < 300) {
          return ApiResponse(
            success: true,
            message: responseData['message'] ?? 'Success',
            data: responseData,
            statusCode: response.statusCode,
          );
        } else {
          return ApiResponse(
            success: false,
            message: responseData['message'] ?? 'Request failed',
            statusCode: response.statusCode,
          );
        }
      } on FormatException {
        return ApiResponse(
          success: false,
          message: 'Invalid response format from server',
          statusCode: response.statusCode,
        );
      }
    } on TimeoutException {
      return ApiResponse(
        success: false,
        message: 'Request timed out. Please try again.',
        statusCode: 408, // Request Timeout
      );
    } on http.ClientException {
      return ApiResponse(
        success: false,
        message: 'Connection failed. Please check your internet connection.',
        statusCode: 503, // Service Unavailable
      );
    } catch (e) {
      if (AppConfig.enableLogging) {
        print('Error in HTTP request: $e');
      }

      return ApiResponse(
        success: false,
        message: 'An unexpected error occurred. Please try again.',
        statusCode: 500,
      );
    }
  }
}
