// lib/models/payslip.dart

class PayPeriod {
  final int periodId;
  final String description;

  PayPeriod({
    required this.periodId,
    required this.description,
  });
}

class PayslipData {
  final String employeeName;
  final double salary;
  final double deductions;
  final double netPay;

  PayslipData({
    required this.employeeName,
    required this.salary,
    required this.deductions,
    required this.netPay,
  });

  factory PayslipData.fromJson(Map<String, dynamic> json) {
    return PayslipData(
      employeeName: json['employeeName'] ?? '',
      salary: json['salary'] ?? 0.0,
      deductions: json['deductions'] ?? 0.0,
      netPay: json['netPay'] ?? 0.0,
    );
  }
}
