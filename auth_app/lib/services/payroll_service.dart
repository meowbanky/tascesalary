// lib/services/payroll_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'session_service.dart';

class PayrollService {
  final String baseUrl;
  final SessionService sessionService;

  PayrollService({
    required this.baseUrl,
    required this.sessionService,
  });

  Map<String, String> get _headers {
    final token = sessionService.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': token != null ? 'Bearer $token' : '',
    };
  }

  Future<List<Map<String, dynamic>>> getPayPeriods() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/payroll/periods.php'),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return List<Map<String, dynamic>>.from(data['data']);
        }
      }
      throw Exception('Failed to load pay periods');
    } catch (e) {
      print('Error in getPayPeriods: $e');
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getPayslip({required int periodId}) async {
    final userId = sessionService.getUserId();
    if (userId == null) {
      throw Exception('Not logged in');
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/payroll/payslip.php').replace(
          queryParameters: {
            'periodId': periodId.toString(),
            'userId': userId,
          },
        ),
        headers: _headers,
      );

      if (response.statusCode == 200) {
        return json.decode(response.body);
      }
      throw Exception('Failed to load payslip');
    } catch (e) {
      print('Error in getPayslip: $e');
      rethrow;
    }
  }

  Future<void> downloadPayslip(int periodId) async {
    final userId = sessionService.getUserId();
    if (userId == null) {
      throw Exception('Not logged in');
    }

    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/payroll/download.php').replace(
          queryParameters: {
            'periodId': periodId.toString(),
            'userId': userId,
          },
        ),
        headers: _headers,
      );

      if (response.statusCode != 200) {
        throw Exception('Failed to download payslip');
      }
    } catch (e) {
      print('Error in downloadPayslip: $e');
      rethrow;
    }
  }
}
