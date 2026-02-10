import 'dart:convert';
import 'package:http/http.dart' as http;
import '../services/session_service.dart';

class PayrollService {
  final String baseUrl;
  final SessionService _sessionService;

  PayrollService({
    required this.baseUrl,
    required SessionService sessionService,
  }) : _sessionService = sessionService;

  Map<String, String> get _headers => {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ${_sessionService.getToken()}',
      };

  Future<List<Map<String, dynamic>>> getPayPeriods() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/payroll/periods'),
        headers: _headers,
      );

      print('Periods Response: ${response.body}'); // Debug print

      if (response.statusCode == 200) {
        final Map<String, dynamic> data = json.decode(response.body);
        if (data['success'] == true) {
          return List<Map<String, dynamic>>.from(data['data']);
        } else {
          throw Exception(data['message'] ?? 'Failed to load pay periods');
        }
      } else {
        throw Exception(
          'Server returned ${response.statusCode}: ${response.body}',
        );
      }
    } catch (e) {
      print('Error in getPayPeriods: $e'); // Debug print
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getPayslip({required int periodId}) async {
    final userId = _sessionService.getUserId();
    if (userId == null) {
      throw Exception('User ID not found in session');
    }

    try {
      final response = await http.get(
        Uri.parse(
            '$baseUrl/api/payroll/payslip.php?periodId=$periodId&userId=$userId'),
        headers: _headers,
      );

      print('Payslip Response: ${response.body}'); // Debug print

      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception(
          'Server returned ${response.statusCode}: ${response.body}',
        );
      }
    } catch (e) {
      print('Error in getPayslip: $e'); // Debug print
      rethrow;
    }
  }
}
