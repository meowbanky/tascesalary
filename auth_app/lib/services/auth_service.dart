// lib/services/auth_service.dart

import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'session_service.dart';

class AuthService extends ChangeNotifier {
  bool _isAuthenticated = false;
  String? _token;
  Map<String, dynamic>? _user;
  String? _error;

  bool get isAuthenticated => _isAuthenticated;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  String? get error => _error;

  Future<void> setUserFromSession(SessionService sessionService) async {
    try {
      _token = sessionService.getToken();
      final userId = sessionService.getUserId();

      if (_token == null || userId == null) {
        throw Exception('No valid session found');
      }

      // Verify token with the server
      final response = await http.get(
        Uri.parse('https://tascesalary.com.ng/auth_api/auth/verify.php'),
        headers: {
          'Authorization': 'Bearer $_token',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          _user = data['user'];
          _isAuthenticated = true;
          _error = null;
          notifyListeners();
        } else {
          throw Exception('Invalid token');
        }
      } else {
        throw Exception('Failed to verify token');
      }
    } catch (e) {
      _token = null;
      _user = null;
      _isAuthenticated = false;
      _error = e.toString();
      notifyListeners();
      throw Exception('Failed to restore session: $e');
    }
  }

  Future<LoginResponse> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('https://tascesalary.com.ng/auth_api/auth/login.php'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      final data = json.decode(response.body);

      if (response.statusCode == 200 && data['success'] == true) {
        _token = data['token'];
        _user = data['user'];
        _isAuthenticated = true;
        _error = null;

        notifyListeners();
        return LoginResponse(
          success: true,
          token: _token,
          userId: data['user']['id'], // adjust based on actual userId field
          user: _user,
        );
      } else {
        _error = data['message'] ?? 'Login failed';
        _isAuthenticated = false;
        notifyListeners();
        return LoginResponse(
          success: false,
          error: _error,
        );
      }
    } catch (e) {
      _error = 'An unexpected error occurred';
      _isAuthenticated = false;
      notifyListeners();
      return LoginResponse(
        success: false,
        error: _error,
      );
    }
  }

  Future<void> logout() async {
    try {
      if (_token != null) {
        await http.post(
          Uri.parse('https://tascesalary.com.ng/auth_api/auth/logout.php'),
          headers: {
            'Authorization': 'Bearer $_token',
            'Content-Type': 'application/json',
          },
        );
      }
    } catch (e) {
      print('Logout error: $e');
    } finally {
      _token = null;
      _user = null;
      _isAuthenticated = false;
      _error = null;
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }

  Future<bool> checkAuthStatus() async {
    return _isAuthenticated && _token != null;
  }
}

// This class encapsulates the response from the login method
class LoginResponse {
  final bool success;
  final String? token;
  final String? userId;
  final Map<String, dynamic>? user;
  final String? error;

  LoginResponse({
    required this.success,
    this.token,
    this.userId,
    this.user,
    this.error,
  });
}
