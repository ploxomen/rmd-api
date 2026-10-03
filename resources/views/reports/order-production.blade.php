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
            <td>
                <strong>
                    {{ $order->order_produc_date_issue->format('d/m/Y') }}
                </strong>
            </td>
        </tr>
        <tr>
            <td class="bg-primary">
                CONTACTO
            </td>
            <td>{{ $ordersDetails['order_contact_name'] }}</td>
            <td class="bg-primary">
                FECHA ENTREGA
            </td>
            <td>
                <strong>
                    {{ $order->order_produc_date_delive->format('d/m/Y') }}
                </strong>
            </td>
        </tr>
        <tr>
            <td class="bg-primary">
                TELÉFONO
            </td>
            <td>{{ $ordersDetails['order_contact_telephone'] }}</td>
            <td class="bg-primary">
                DIRECCIÓN DE ENTREGA
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
                <th>IMAGEN REF.</th>
                <th>PRODUCTO / DESCRIPCIÓN</th>
                <th>CANT.</th>
                <th>UND.</th>
                <th>HR. TOT. ESCANDALLO.</th>
                <th>ÁREAS</th>
                <th>HH/UND.</th>
                <th>TOTAL HH</th>
            </tr>
        </thead>
        <tbody style="font-size: 10px;">
            @foreach ($details as $keyDetail => $detail)
                @php
                    $pathImg = $detail->product_img;
                    $rowSpan = $detail->list_labels->count();
                    $urlImage = empty($pathImg) || !\File::exists($pathImg) ? null : $pathImg;
                @endphp
                @foreach ($detail->list_labels as $key => $label)
                    <tr>
                        @if ($key === 0)
                            <td rowspan="{{ $rowSpan }}" style="text-align: center;">
                            @empty(!$urlImage)
                                <img src="{{ public_path($urlImage) }}" alt="Imagen de productos" width="40px"
                                    height="40px">
                            @endempty
                        </td>
                        <td rowspan="{{ $rowSpan }}" style="line-height:0.8; font-size: 11px;">
                            {{ $detail->product_name }}
                        @empty(!$detail->product_description)
                            {!! $detail->product_description !!}
                        @endempty
                    </td>
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">{{ $detail->amount }}</td>
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">Unidad</td>
                @endif
                {{-- Celdas repetidas por cada subítem/label --}}
                <td style="text-align: center;">{{ $label->pro_escandallo_total }} HH</td>
                <td style="text-align: center;">{{ $label->product_label_name }}</td>
                <td style="text-align: center;">{{ $label->time_origin_hours }} HH</td>
                {{-- Celda acumulada con rowSpan en la última columna --}}
                @if ($key === 0)
                    <td rowspan="{{ $rowSpan }}" style="text-align: center;">{{ $detail->subtotal }} HH
                    </td>
                @endif
            </tr>
        @endforeach
        @php
            $total += $detail->subtotal;
        @endphp
    @endforeach

</tbody>
<tfoot>
    <tr>
        <th colspan="5" style="text-align: right;">TOTAL HH</th>
        <th colspan="3" style="text-align: left;">{{ $total }} HH</th>
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
<td>
    <td colspan="3" style="width: 100%; height: 30px;"></td>
</td>
<tr style="background-color: #ffcfaf;">
    <td style="padding: 4px; text-align: right;">COMERCIAL:</td>
    <td style="width: 250px;"></td>
    <td style="padding: 4px;">{{ $userName }}</td>
</tr>
</table>
</body>

</html>
