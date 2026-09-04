<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">
    <title>Cotización</title>
</head>

<body>
    @include('styles.pdfv2Style')
    <style>
        @page {
            margin: 20px;
            margin-bottom: 60px;
        }

        .bg-primary {
            background-color: #4CA746;
            color: #FFFFFF;
        }

        .mb {
            margin-bottom: 20px;
        }

        .mb-2 {
            margin-bottom: 12px;
        }


        .seccion-information {
            padding: 4px 8px;
            font-size: 14px;
        }

        .table-information {
            font-size: 12px;
            vertical-align: middle;
        }

        .table-information td,
        .table-information th {
            border: 1px solid rgb(94, 92, 92);
            padding: 2px 6px;
        }

        footer {
            position: fixed;
            left: 35%;
            right: 0px;
            height: 150px;
            bottom: -140px;
        }
    </style>
    @php
        $ordersDetails = $order->numberOrders();
        $total = 0;
    @endphp
    <header>
        <span
            style="font-size: 13px; display: block; text-align: center; font-weight: 600; background-color: #F2F2F2; font-style: italic;letter-spacing: 10%">PARQUES
            INFANTILES, CIRCUITOS CANINOS, SUPERFICIES DE SEGURIDAD, MOBILIARIO URBANO</span>
        <table class="table-img">
            <tr>
                <td style="text-align: left;">
                    <img src="{{ public_path('img/logo-izquierda.png') }}" alt="Logo" width="180px">
                </td>
                <td style="text-align: center;">
                    <img src="{{ public_path('img/logo2.png') }}" alt="Logo" width="150px">
                </td>
                <td style="text-align: right;">
                    <img src="{{ public_path('img/logo-derecha.png') }}" alt="Logo" width="140px">
                </td>
            </tr>
        </table>
    </header>
    <footer>
        <img src="{{ public_path('img/logo-footer.png') }}" alt="Logo" width="200px">
    </footer>
    <div class="bg-primary mb"
        style="font-size: 16px; padding: 4px; text-align: center; font-weight: 700; line-height: 1">
        <span>ORDEN DE PRODUCCIÓN Nº - {{ $order->order_production_code }}</span>
    </div>
    <div class="seccion-information bg-primary mb-2">
        <span>INFORMACIÓN GENERAL</span>
    </div>
    <table class="mb-2 table-information">
        <tr>
            <td class="bg-primary" style="width: 80px;">
                CLIENTE
            </td>
            <td style="width: 250px;">
                {{ $order->customer->customer_name }}
            </td>
            <td class="bg-primary" style="width: 100px;">
                N° ORDEN
            </td>
            <td style="width: 250px;">{{ $order->order_production_code }}</td>
        </tr>
        <tr>
            <td class="bg-primary">
                RUC
            </td>
            <td>
                {{ $order->customer->customer_number_document }}
            </td>
            <td class="bg-primary">
                N° PEDIDO REF
            </td>
            <td>{{ $ordersDetails['order_code'] }}</td>
        </tr>
        <tr>
            <td class="bg-primary">
                PROYECTO
            </td>
            <td>{{ $ordersDetails['order_project'] }}</td>
            <td class="bg-primary">
                FECHA EMISIÓN
            </td>
            <td>{{ $order->order_produc_date_issue }}</td>
        </tr>
        <tr>
            <td class="bg-primary">
                CONTACTO
            </td>
            <td>{{ $ordersDetails['order_contact_name'] }}</td>
            <td class="bg-primary">
                FECHA ENTREGA
            </td>
            <td>{{ $order->order_produc_date_delive }}</td>
        </tr>
        <tr>
            <td class="bg-primary">
                TELÉFONO
            </td>
            <td>{{ $ordersDetails['order_contact_telephone'] }}</td>
            <td class="bg-primary">
                CONDICIONES Y DIRECCIÓN DE ENTREGA
            </td>
            <td>{{ $order->order_produc_address }}</td>
        </tr>
    </table>
    <div class="seccion-information bg-primary mb-2">
        <span>DETALLE DE PRODUCCIÓN</span>
    </div>
    <table class="table-information mb">
        <thead>
            <tr class="bg-primary">
                <th>COT.</th>
                <th>PRODUCTO / DESCRIPCIÓN.</th>
                <th>CANT.</th>
                <th>HR. TOT. ESCANDALLO.</th>
                <th>SECCIÓN</th>
                <th>HORAS SECCIÓN</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($details as $detail)
                @php
                    $rowSpan = $detail->list_labels->count();
                @endphp
                <tr>
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">{{ $detail->order_code }}</td>
                    <td rowspan="{{ $rowSpan }}">{{ $detail->product_name }}</td>
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">{{ $detail->amount }}</td>
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">{{ $detail->subtotal }}h</td>
                    @foreach ($detail->list_labels as $key => $label)
                        @if ($key > 0)
                <tr>
            @endif
            <td style="text-align: center;">{{ $label->product_label_name }}</td>
            <td>{{ $label->time_origin_hours }}h</td>
            </tr>
            @endforeach
            @php
                $total += $detail->subtotal;
            @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;">TOTAL HORAS PREVISTAS</th>
                <th style="text-align: left;">{{ $total }}h</th>
            </tr>
        </tfoot>
    </table>
    <table class="table-information" style="margin-bottom: 100px;">
        <tr class="bg-primary">
            <td>OBSERVACIONES</td>
        </tr>
        <tr>
            <td>{{ $order->order_production_detail }}</td>
        </tr>
    </table>
    <table class="mb" style="text-align: center; font-size: 12px;">
        <tr>
            <td style="padding-top: 4px; border-top: 1px solid black;">Responsable de Producción</td>
            <td style="width: 250px;"></td>
            <td style="padding-top: 4px; border-top: 1px solid black;">Responsable Arquitectura</td>
        </tr>
    </table>
</body>

</html>
