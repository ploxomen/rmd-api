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

        .bg-secondary {
            background-color: #424242;
            color: #FFFFFF;
        }

        .table-cliente {
            font-size: 12px;
            line-height: 1;
            font-weight: 500;
        }

        .table-cliente th {
            text-align: left;
            padding-bottom: 4px;
        }

        .subtitulo {
            padding: 6px 20px;
            font-weight: 700;
            width: 150px;
            line-height: 1;
        }

        .table-quotations thead th {
            font-size: 10px;
            line-height: 1;
            font-weight: 700;
            border: 1px solid black;
            padding: 4px;
        }

        .table-quotations tbody td {
            font-size: 11px;
            border: 1px solid black;
        }

        .table-quotations tfoot th,
        .table-quotations tfoot td {
            font-size: 12px;
            line-height: 1;
            font-weight: 600;
            text-align: center;
            padding: 4px 12px;
            border: 1px solid black;
        }

        .table-img td {
            padding: 8px 0;
        }

        .table-detalle {
            font-size: 12px;
            font-weight: 500;
            line-height: 1;
        }

        .table-detalle th {
            padding: 4px 20px;
            text-align: left;
            border: 1px solid black;
        }

        .table-detalle td {
            border: 1px solid black;
            padding: 4px;
        }

        .table-user {
            font-size: 12px;
            font-weight: 400;
            line-height: 1;
            text-align: center;
            width: 290px;
            margin-left: auto;
        }

        .title-user {
            font-size: 14px;
        }
        /* header{
            position: fixed;
            left: 0px;
            right: 0px;
            height: 150px;
            margin-top: -150px;
        } */
        footer{
            position: fixed;
            left: 35%;
            right: 0px;
            height: 150px;
            bottom: -140px;
        }
    </style>
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
    @if ($order->order_status === 0)
        <div class="anulado">
            <span>ANULADO</span>
        </div>
    @endif
    <div class="bg-primary mb"
        style="font-size: 28px; padding: 4px; text-align: center; font-weight: 700; line-height: 1">
        <span>PEDIDO - {{ $order->order_code }}</span>
    </div>
    <table class="mb-2">
        <tr>
            <td class="bg-secondary subtitulo">
                CLIENTE
            </td>
            <td style="width: 350px;"></td>
            <td style="font-weight: 700; font-size: 14px; width: 50px;">
                FECHA
            </td>
            <td style="font-size: 14px;">{{ date('d/m/Y', strtotime($order->created_at)) }}</td>
        </tr>
    </table>
    <table class="table-cliente mb">
        <tr>
            <th style="width: 100px;">
                NOMBRE
            </th>
            <td style="width: 300px;">{{ $order->customer->customer_name }}</td>
            <th style="width: 100px;">
                CONTACTO
            </th>
            <td>{{ $order->order_contact_name }}</td>
        </tr>
        <tr>
            <th>
                RUC
            </th>
            <td>{{ $order->customer->customer_number_document }}</td>
            <th>
                EMAIL
            </th>
            <td>{{ $order->order_contact_email }}</td>
        </tr>
        <tr>
            <th>
                PROYECTO
            </th>
            <td>{{ $order->order_project }}</td>
            <th>
                TELEFONO
            </th>
            <td>{{ $order->order_contact_telephone }}</td>
        </tr>
    </table>
    <table class="mb-2">
        <tr>
            <td class="bg-secondary subtitulo">
                PRODUCTOS
            </td>
            <td></td>
        </tr>
    </table>
    <table class="mb table-quotations">
        <thead>
            <tr class="bg-primary">
                <th style="width: 50px;">
                    COT.
                </th>
                <th>
                    FOTO
                </th>
                <th>
                    DESCRIPCION
                </th>
                <th style="width: 80px;">
                    CANT.
                </th>
                <th style="width: 90px;">
                    PU
                </th>
                <th style="width: 90px;">
                    TOTAL
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->quotations as $quotation)
                @php
                    $money = $quotation->quotation_type_money == 'PEN' ? 'S/' : '$';
                @endphp
                @foreach ($quotation->products as $product)
                    @php
                        $pathImg = $product->product_img;
                        $urlImage = empty($pathImg) || !\File::exists($pathImg) ? null : $pathImg;
                        $price = $product->pivot->detail_price_unit + $product->pivot->detail_price_additional;
                    @endphp
                    <tr>
                        <td style="text-align: center;">{{ $quotation->quotation_code }}</td>
                        <td style="text-align: center; padding: 5px;">
                            @empty(!$urlImage)
                                <img src="{{ public_path($urlImage) }}" alt="Imagen de productos" width="60px"
                                    height="60px">
                            @endempty
                        </td>
                        <td style="padding-left: 5px; line-height:0.8; font-size: 11px;">
                            {{ $product->product_name }}
                            @empty(!$product->pivot->quotation_description)
                                <br>{!! $product->pivot->quotation_description !!}
                            @endempty
                        </td>
                        <td style="text-align: center;">{{ $product->pivot->detail_quantity }}</td>
                        <td style="text-align: center;">{{ $money . number_format($price, 2) }}</td>
                        <td style="text-align: center;">
                            {{ $money . number_format($product->pivot->detail_total, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="border: none;"></td>
                <th colspan="2">
                    SUBTOTAL
                </th>
                <td>{{ $moneyOrder . '' . $order->order_mount }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none;"></td>
                <th colspan="2">IGV 18%</th>
                <td>{{ $moneyOrder . '' . $order->order_mount_igv }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border: none;"></td>
                <th colspan="2">TOTAL</th>
                <td>{{ $moneyOrder . '' . $order->order_total }}</td>
            </tr>
        </tfoot>
    </table>
    <table class="mb-2">
        <tr>
            <td class="bg-secondary subtitulo">
                DETALLES
            </td>
            <td></td>
        </tr>
    </table>
    <table class="mb table-detalle">
        <tr>
            <th style="width: 160px;">CONDICIONES DE PAGO</th>
            <td colspan="3">{{ $order->order_conditions_pay }}</td>
        </tr>
        <tr>
            <th>CONDICIONES DE ENTREGA</th>
            <td colspan="3">{{ $order->order_conditions_delivery }}</td>
        </tr>
        <tr>
            <th>FECHA ENTREGA</th>
            <td colspan="3">{{ date('d/m/Y', strtotime($order->order_date_issue)) }}</td>
        </tr>
        <tr>
            <th>DIRECCION ENTREGA</th>
            <td colspan="3">{{ $order->order_address }}</td>
        </tr>
        <tr>
            <th>DEPARTAMENTO</th>
            <td style="width: 200px;">{{ $order->district->departament->departament_name }}</td>
            <td style="width: 90px; border:none;"></td>
            <td rowspan="3" style="border:none; padding-right: 0; padding-bottom: 0;"></td>
        </tr>
        <tr>
            <th>PROVINCIA</th>
            <td>{{ $order->district->province->province_name }}</td>
            <td style="border:none;"></td>
        </tr>
        <tr>
            <th>DISTRITO</th>
            <td>{{ $order->district->district_name }}</td>
            <td style="border:none;"></td>
        </tr>
    </table>
    <table class="table-user" border="1">
        <tr>
            <td>
                <span>Asesor comercial</span><br>
                <span
                    class="title-user">{{ $order->user->user_name . ' ' . $order->user->user_last_name }}</span>
            </td>
        </tr>
        <tr>
            <td>CEL: {{ $order->user->user_cell_phone }}</td>
        </tr>
        <tr>
            <td>
                @foreach ($listEmails as $key => $email)
                    <a class="text-email">{{ $email }}</a>
                    @if ($key + 1 < count($listEmails))
                        <span style="padding: 2px 0;" class="text-email"> | </span>
                    @endif
                @endforeach
            </td>
        </tr>
    </table>
</body>

</html>
