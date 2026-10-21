<?php
$payload = [
    'header' => [
        'C_DocOrigenNum' => 1,
        'U_TipoNC' => '01',
        'U_FacSerie' => 'E34',
        'Comments' => 'Devolución de artículo por auditoría',
        'DocDate' => '2026-10-19',
        'devolver_efectivo' => 1
    ],
    'lines' => [
        [
            'LineNum' => 0,
            'BaseEntry' => 1,
            'BaseLine' => 0,
            'ItemCode' => 'DES0001',
            'Dscription' => 'ARTICULO DESECHABLE',
            'Quantity' => 1,
            'QtyDisponible' => 1,
            'Price' => 350.00,
            'PriceBefDi' => 350.00,
            'DiscPrcnt' => 0,
            'VatPrcnt' => 0,
            'TaxCode' => '',
            'WhsCode' => '01'
        ]
    ]
];

$opts = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($opts);
$response = file_get_contents('http://localhost:8000/index.php?action=guardar_nota_credito', false, $context);

echo "HTTP Response:\n" . $response . "\n";
