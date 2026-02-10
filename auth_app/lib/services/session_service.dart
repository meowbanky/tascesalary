// lib/services/session_service.dart

import 'package:shared_preferences/shared_preferences.dart';

class SessionService {
  static const String _tokenKey = 'auth_token';
  static const String _userIdKey = 'user_id';
  final SharedPreferences _prefs;

  SessionService(this._prefs);

  Future<void> saveSession({
    required String token,
    required String userId, Map<String, dynamic>? userData,
  }) async {
    await _prefs.setString(_tokenKey, token);
    await _prefs.setString(_userIdKey, userId);
  }

  String? getToken() {
    return _prefs.getString(_tokenKey);
  }

  String? getUserId() {
    return _prefs.getString(_userIdKey);
  }

  bool isLoggedIn() {
    return _prefs.getString(_tokenKey) != null;
  }

  Future<void> clearSession() async {
    await _prefs.remove(_tokenKey);
    await _prefs.remove(_userIdKey);
  }
}
