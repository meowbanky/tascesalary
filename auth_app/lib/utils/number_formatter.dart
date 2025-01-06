// lib/utils/number_formatter.dart
class NumberFormatter {
  static String formatMoney(double amount) {
    // Convert to absolute value for formatting
    var absoluteAmount = amount.abs();

    // Convert to string with 2 decimal places
    String withDecimals = absoluteAmount.toStringAsFixed(2);

    // Split into whole and decimal parts
    var parts = withDecimals.split('.');
    var whole = parts[0];
    var decimal = parts[1];

    // Add commas for thousands
    var formattedWhole = '';
    for (var i = 0; i < whole.length; i++) {
      if (i > 0 && (whole.length - i) % 3 == 0) {
        formattedWhole += ',';
      }
      formattedWhole += whole[i];
    }

    // Return formatted string with sign and symbol
    var sign = amount < 0 ? '-' : '';
    return '₦$sign$formattedWhole.$decimal';
  }
}

// lib/utils/number_formatter.dart

// String formatCurrency(double amount) {
//   return '\$${amount.toStringAsFixed(2)}';
// }
