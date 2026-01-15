// lib/screens/payslip_screen.dart
import 'package:flutter/material.dart';
import '../services/payroll_service.dart';
import '../models/payslip.dart';
import '../utils/number_formatter.dart';

String formatCurrency(double amount) {
  return NumberFormatter.formatMoney(amount);
}

class PayslipScreen extends StatefulWidget {
  final PayrollService payrollService;

  const PayslipScreen({
    Key? key,
    required this.payrollService,
  }) : super(key: key);

  @override
  State<PayslipScreen> createState() => _PayslipScreenState();
}

class _PayslipScreenState extends State<PayslipScreen> {
  late final PayrollService _payrollService;
  PayPeriod? selectedPeriod;
  bool isLoading = true;
  bool isDownloading = false;
  List<PayPeriod> periods = [];
  PayslipData? payslipData;

  @override
  void initState() {
    super.initState();
    _payrollService = widget.payrollService;
    _loadPayPeriods();
  }

  Future<void> _downloadPayslip() async {
    if (selectedPeriod == null || isDownloading) return;

    setState(() => isDownloading = true);

    try {
      await _payrollService.downloadPayslip(selectedPeriod!.periodId);
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Payslip downloaded successfully'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      _showError('Failed to download payslip');
    } finally {
      if (mounted) setState(() => isDownloading = false);
    }
  }

  Future<void> _loadPayPeriods() async {
    if (!mounted) return;

    setState(() => isLoading = true);
    try {
      final periodsData = await _payrollService.getPayPeriods();
      if (!mounted) return;

      setState(() {
        periods = periodsData
            .map((period) => PayPeriod(
                  periodId: int.parse(period['periodId'].toString()),
                  description: period['description'].toString(),
                ))
            .toList();
        if (periods.isNotEmpty) {
          selectedPeriod = periods.first;
          _fetchPayslip(periods.first.periodId);
        }
      });
    } catch (e) {
      if (!mounted) return;
      _showError('Failed to load pay periods');
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  Future<void> _fetchPayslip(int periodId) async {
    if (!mounted) return;

    setState(() => isLoading = true);
    try {
      final response = await _payrollService.getPayslip(periodId: periodId);
      if (!mounted) return;

      if (response['success'] == true && response['data'] != null) {
        setState(() => payslipData = PayslipData.fromJson(response['data']));
      } else {
        throw Exception('Invalid response format');
      }
    } catch (e) {
      if (!mounted) return;
      _showError('Failed to load payslip details');
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.red),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Payslip')),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                DropdownButton<PayPeriod>(
                  value: selectedPeriod,
                  items: periods.map((period) {
                    return DropdownMenuItem(
                      value: period,
                      child: Text(period.description),
                    );
                  }).toList(),
                  onChanged: (newPeriod) {
                    setState(() => selectedPeriod = newPeriod);
                    _fetchPayslip(newPeriod!.periodId);
                  },
                ),
                const SizedBox(height: 16),
                payslipData != null
                    ? Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Employee: ${payslipData!.employeeName}'),
                            Text('Salary: ${formatCurrency(payslipData!.salary)}'),
                            Text('Deductions: ${formatCurrency(payslipData!.deductions)}'),
                            Text('Net Pay: ${formatCurrency(payslipData!.netPay)}'),
                          ],
                        ),
                      )
                    : const Text('No payslip data available'),
                ElevatedButton(
                  onPressed: _downloadPayslip,
                  child: isDownloading
                      ? const CircularProgressIndicator()
                      : const Text('Download Payslip'),
                ),
              ],
            ),
    );
  }
}
