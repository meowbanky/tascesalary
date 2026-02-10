// lib/widgets/payslip_row.dart
import 'package:flutter/material.dart';
import '../utils/number_formatter.dart';

class PayslipRow extends StatelessWidget {
  final String label;
  final double value;
  final bool isDeduction;
  final bool isTotal;

  const PayslipRow({
    Key? key,
    required this.label,
    required this.value,
    this.isDeduction = false,
    this.isTotal = false,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: TextStyle(
                fontSize: isTotal ? 18 : 16,
                fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              ),
            ),
          ),
          Text(
            NumberFormatter.formatMoney(isDeduction ? -value : value),
            style: TextStyle(
              fontSize: isTotal ? 18 : 16,
              fontWeight: isTotal ? FontWeight.bold : FontWeight.normal,
              color: isDeduction
                  ? Colors.red
                  : isTotal
                      ? Colors.blue
                      : null,
            ),
          ),
        ],
      ),
    );
  }
}
