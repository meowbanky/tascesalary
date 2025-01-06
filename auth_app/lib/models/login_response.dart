// lib/models/login_response.dart

class LoginResponse {
  final bool success;
  final String? error;
  final String? userId;
  final String? token;
  final Map<String, dynamic>? user;

  LoginResponse({
    required this.success,
    this.error,
    this.userId,
    this.token,
    this.user,
  });

  factory LoginResponse.fromJson(Map<String, dynamic> json) {
    return LoginResponse(
      success: json['success'] ?? false,
      error: json['error'],
      userId: json['user']?['id']?.toString(),
      token: json['token'],
      user: json['user'],
    );
  }
}
