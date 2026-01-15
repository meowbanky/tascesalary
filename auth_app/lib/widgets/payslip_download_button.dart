// lib/widgets/payslip_download_button.dart

import 'package:flutter/material.dart';
import '../services/payroll_service.dart'; // Updated import

class PayslipDownloadButton extends StatefulWidget {
  final PayrollService payrollService; // Updated type
  final int periodId;

  const PayslipDownloadButton({
    Key? key,
    required this.payrollService, // Updated parameter name
    required this.periodId,
  }) : super(key: key);

  @override
  State<PayslipDownloadButton> createState() => _PayslipDownloadButtonState();
}

class _PayslipDownloadButtonState extends State<PayslipDownloadButton> {
  bool _isLoading = false;

  Future<void> _downloadPayslip() async {
    if (_isLoading) return;

    setState(() {
      _isLoading = true;
    });

    try {
      await widget.payrollService
          .downloadPayslip(widget.periodId); // Updated method call

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Payslip downloaded successfully'),
            backgroundColor: Colors.green,
            behavior: SnackBarBehavior.floating,
            duration: Duration(seconds: 3),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to download payslip: ${e.toString()}'),
            backgroundColor: Colors.red,
            behavior: SnackBarBehavior.floating,
            duration: Duration(seconds: 5),
            action: SnackBarAction(
              label: 'RETRY',
              textColor: Colors.white,
              onPressed: _downloadPayslip,
            ),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return ElevatedButton.icon(
      onPressed: _isLoading ? null : _downloadPayslip,
      icon: _isLoading
          ? const SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
              ),
            )
          : const Icon(Icons.download),
      label: Text(_isLoading ? 'Downloading...' : 'Download Payslip'),
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    );
  }
}
