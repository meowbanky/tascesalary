// lib/models/user.dart

class User {
  final String id;
  final String name;
  final String? email;
  final String? department;
  final String? grade;
  final String? step;

  User({
    required this.id,
    required this.name,
    this.email,
    this.department,
    this.grade,
    this.step,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'].toString(),
      name: json['name'] ?? '',
      email: json['email'],
      department: json['department'],
      grade: json['grade']?.toString(),
      step: json['step']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'department': department,
      'grade': grade,
      'step': step,
    };
  }
}
