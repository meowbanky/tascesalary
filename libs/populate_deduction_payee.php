<?php
require_once 'App.php';
$App = new App();

// 1. Create the table
$sql = file_get_contents(__DIR__ . '/../database/create_deduction_payee_table.sql');
$App->link->exec($sql);

// 2. Mapping of deduction description keywords to payee details
$mappings = [
    'VOTED' => [
        ['payee_name' => 'VOTED TASCE (WELAFRE)', 'bank_name' => 'U.B.A', 'account_no' => '2074402849', 'percentage' => 100]
    ],
    'STAFF SCH' => [
        ['payee_name' => 'STAFF SCH', 'bank_name' => 'U.B.A', 'account_no' => '2065795392', 'percentage' => 100]
    ],
    'MUSLIM' => [
        ['payee_name' => 'TASCE MUSLIM COMM', 'bank_name' => 'STERLING BANK', 'account_no' => '0013190977', 'percentage' => 100]
    ],
    'BED' => [
        ['payee_name' => 'BUS. EDU TASCE OMU', 'bank_name' => 'FIRST BANK', 'account_no' => '2023248779', 'percentage' => 100]
    ],
    'DRIVER' => [
        ['payee_name' => 'TASCE DRIVER ASSOCIATION', 'bank_name' => 'F.C.M.B', 'account_no' => '2408618015', 'percentage' => 100]
    ],
    'NAS.R.B.S' => [
        ['payee_name' => 'NAS TASCE WELFARE & RETIREMENT BENEFIT', 'bank_name' => 'STANBIC IBTC', 'account_no' => '0006041853', 'percentage' => 100]
    ],
    'NAS COOP' => [
        ['payee_name' => 'NASCOOP OMU', 'bank_name' => 'STERLING BANK', 'account_no' => '0016257996', 'percentage' => 100]
    ],
    'GEN.COOP' => [
        ['payee_name' => 'TASCE GEN.COOP.', 'bank_name' => 'F.C.M.B', 'account_no' => '4691309010', 'percentage' => 100]
    ],
    'SNR COOP' => [
        ['payee_name' => 'TASCE SNR. COOP', 'bank_name' => 'U.B.A', 'account_no' => '1013497009', 'percentage' => 100]
    ],
    'CHRISTIAN' => [
        ['payee_name' => 'TASCE CHRISTIAN COMMUNITY', 'bank_name' => 'FIRST BANK', 'account_no' => '2041314924', 'percentage' => 100]
    ],
    'INVESTMENT' => [
        ['payee_name' => 'TASCE FARM', 'bank_name' => 'ZENITH BANK', 'account_no' => '1016662987', 'percentage' => 100]
    ],
    'NASU' => [
        ['payee_name' => 'TASCE NASU OMU IJEBU', 'bank_name' => 'UNION BANK', 'account_no' => '0043075800', 'percentage' => 100]
    ],
    'COEASU' => [
        ['payee_name' => 'COEASU SOUTHWEST ZONAL', 'bank_name' => 'ZENITH BANK', 'account_no' => '1015347841', 'fixed_amount' => 15000, 'percentage' => 0],
        ['payee_name' => 'COEASU TASCE', 'bank_name' => 'F.C.M.B', 'account_no' => '2440987018', 'fixed_amount' => 0, 'percentage' => 70],
        ['payee_name' => 'COEASU NATIONAL', 'bank_name' => 'FIRST BANK', 'account_no' => '2004162245', 'fixed_amount' => 0, 'percentage' => 30]
    ],
    'SSUCEN' => [
        ['payee_name' => 'SSUCEN DUES', 'bank_name' => 'F.C.M.B', 'account_no' => '0739754011', 'percentage' => 100]
    ],
    'LEADWAY' => [
        ['payee_name' => 'LEADWAY ASSURANCE', 'bank_name' => 'G.T.B', 'account_no' => '0256202293', 'percentage' => 100]
    ],
    'ASCOMSOLT' => [
        ['payee_name' => 'ASCOMSOLT', 'bank_name' => 'U.B.A', 'account_no' => '2050901371', 'percentage' => 100]
    ],
    'MEDICAL' => [
        ['payee_name' => 'SACOETEC DRUG REVOLVING SCHEME', 'bank_name' => 'ZENITH BANK', 'account_no' => '1310202667', 'percentage' => 100]
    ],
    'TAX' => [
         ['payee_name' => 'TAX', 'bank_name' => '', 'account_no' => '', 'percentage' => 100]
    ],
    'PENSION' => [
         ['payee_name' => 'PENSION', 'bank_name' => '', 'account_no' => '', 'percentage' => 100]
    ]
];

// 3. Clear existing data (optional, but good for rerun)
$App->link->exec("DELETE FROM tbl_deduction_payee");

// 4. Populate
$deductions = $App->selectAll("SELECT ed_id, ed, edDesc FROM tbl_earning_deduction WHERE type = 2", []);

foreach ($deductions as $d) {
    foreach ($mappings as $keyword => $payees) {
        if (stripos($d['ed'], $keyword) !== false || stripos($d['edDesc'], $keyword) !== false) {
            foreach ($payees as $p) {
                $query = "INSERT INTO tbl_deduction_payee (ed_id, payee_name, bank_name, account_no, fixed_amount, percentage) 
                          VALUES (:ed_id, :payee_name, :bank_name, :account_no, :fixed_amount, :percentage)";
                $params = [
                    ':ed_id' => $d['ed_id'],
                    ':payee_name' => $p['payee_name'],
                    ':bank_name' => $p['bank_name'],
                    ':account_no' => $p['account_no'],
                    ':fixed_amount' => $p['fixed_amount'] ?? 0,
                    ':percentage' => $p['percentage'] ?? 0
                ];
                $App->executeNonSelect($query, $params);
            }
            echo "Inserted payee(s) for deduction: " . $d['ed'] . " (" . $keyword . ")\n";
            break; // Move to next deduction
        }
    }
}

echo "Done populating tbl_deduction_payee.\n";
?>
