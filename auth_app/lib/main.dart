// lib/main.dart

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'services/auth_service.dart';
import 'services/session_service.dart';
import 'services/payroll_service.dart';
import 'screens/splash_screen.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();

  runApp(MyApp(prefs: prefs));
}

class MyApp extends StatelessWidget {
  final SharedPreferences prefs;

  const MyApp({
    Key? key,
    required this.prefs,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Create SessionService instance
    final sessionService = SessionService(prefs);

    return MultiProvider(
      providers: [
        Provider<SessionService>.value(
          value: sessionService,
        ),
        ChangeNotifierProvider(
          create: (_) => AuthService(),
        ),
      ],
      child: MaterialApp(
        title: 'TASCE Salary',
        theme: ThemeData(
          primarySwatch: Colors.blue,
          visualDensity: VisualDensity.adaptivePlatformDensity,
        ),
        home: const SplashScreen(),
        routes: {
          '/login': (context) => LoginScreen(),
          '/dashboard': (context) {
            final payrollService = PayrollService(
              baseUrl: 'https://tascesalary.com.ng/auth_api',
              sessionService: sessionService,
            );
            return DashboardScreen(payrollService: payrollService);
          },
        },
      ),
    );
  }
}
