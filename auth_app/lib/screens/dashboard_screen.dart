import 'package:flutter/material.dart';
import '../services/payroll_service.dart';

class DashboardScreen extends StatefulWidget {
  final PayrollService payrollService;

  DashboardScreen({Key? key, required this.payrollService}) : super(key: key);

  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  List<Map<String, dynamic>> payPeriods = [];
  bool isLoading = false;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadPayPeriods();
  }

  Future<void> _loadPayPeriods() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });
    try {
      payPeriods = await widget.payrollService.getPayPeriods();
    } catch (e) {
      errorMessage = 'Error loading pay periods: ${e.toString()}';
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _viewPayslip(int periodId) async {
    setState(() => isLoading = true);
    try {
      final payslip =
          await widget.payrollService.getPayslip(periodId: periodId);
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('Payslip Details'),
          content: Text(
              payslip.toString()), // Displaying raw data; customize as needed
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('Close'),
            ),
          ],
        ),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error viewing payslip: ${e.toString()}')),
      );
    } finally {
      setState(() => isLoading = false);
    }
  }

  Future<void> _downloadPayslip(int periodId) async {
    setState(() => isLoading = true);
    try {
      await widget.payrollService.downloadPayslip(periodId);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Payslip downloaded successfully')),
      );
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error downloading payslip: ${e.toString()}')),
      );
    } finally {
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Dashboard'),
      ),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : errorMessage != null
              ? Center(child: Text(errorMessage!))
              : ListView.builder(
                  itemCount: payPeriods.length,
                  itemBuilder: (context, index) {
                    final period = payPeriods[index];
                    return ListTile(
                      title: Text('Period: ${period['period']}'),
                      subtitle: Text(
                          'From ${period['start_date']} to ${period['end_date']}'),
                      trailing: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          IconButton(
                            icon: Icon(Icons.visibility),
                            onPressed: () => _viewPayslip(period['id']),
                            tooltip: 'View Payslip',
                          ),
                          IconButton(
                            icon: Icon(Icons.download),
                            onPressed: () => _downloadPayslip(period['id']),
                            tooltip: 'Download Payslip',
                          ),
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
