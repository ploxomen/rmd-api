<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <title>Cotización</title>
</head>
<body>
    <style>
        html{
            font-family: "Roboto", sans-serif;
        }
        .d-flex{
            display: flex;
        }
        .title {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 500;
        }
        .parragraph-small{
            margin: 0;
            margin-bottom: 15px;
            font-size: 13px;
        }
        .text-column{
            font-size: 12px;
            text-align: left;
            color:#a8a8a8;
            font-style:italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            vertical-align: middle;
        }
        table tfoot td,
        table tfoot th{
            text-align: right;
        }
        p{
            margin-top: 0;
        }
        .table-details thead th{
            border-left: 1px solid black;
            border-right: 1px solid black;
            border-top: 1px solid black;
            border-bottom: 1px solid black;
        }
        .table-details tbody td{
            border-left: 1px solid black;
            border-right: 1px solid black;
        }
        .table-details tfoot{
            border-top: 1px solid black;
        }
        .table-details tfoot th{
            padding-right: 10px;
        }
        .table-details tfoot td{
            border-bottom: 1px solid black;
            border-left: 1px solid black;
            border-right: 1px solid black;
        }
    </style>
    @php
        $emails = $configuration->where('description','=','business_email')->first()->value;
        $phones = $configuration->where('description','=','business_cell_phone')->first()->value;
        $pages = $configuration->where('description','=','business_page')->first()->value;
        $pagesArray = explode('/',$pages);
        $listEmails = implode('<br>',explode('/',$emails));
        $listPhones = implode(' / CEL: ',explode('/',$phones));
        $money = $quotation->quotation_type_money == 'PEN' ? 'S/' : '$';
        $totalDetails = $quotation->products()->count();
    @endphp
    <table style="margin-bottom: 20px;">
        <tr>
            <td style="width: 420px;">
                <img src={{public_path('img/rmd-header.jpg')}} alt="imagen" width="300px">
                <h1 class="title">{{$configuration->where('description','=','business_name')->first()->value}}</h1>
                <strong class="title">RUC: {{$configuration->where('description','=','business_ruc')->first()->value}}</strong>
                <p class="parragraph-small">{{$configuration->where('description','=','business_address')->first()->value}}</´p>
                <p class="parragraph-small">
                    CEL: {!! $listPhones !!}
                </p>
                <p style="margin-top:0; text-decoration: underline; color:#5c5cff;">
                    {!! $listEmails !!}
                </p>
                <img src={{public_path('img/rmd-list-description.png')}} alt="imagen" width="400px">
            </td>
            <td>
                <table style="vertical-align: middle;">
                    <tr>
                        <td></td>
                        <td style="width: 150px;">
                            <h2 style="margin-bottom: 20px; margin-top: 0; text-align: center; color:#a8a8a8; font-weight: 900;">PRESUPUESTO</h2>
                            <table>
                                <tr>
                                    <th class="text-column" style="width: 105px;">FECHA:</th>
                                    <td style="font-size: 12px;">{{date('d/m/Y',strtotime($quotation->quotation_date_issue))}}</td>
                                </tr>
                                <tr>
                                    <th class="text-column">N° de presupuesto</th>
                                    <td style="font-size: 12px;">{{str_pad($quotation->id,5,'0',STR_PAD_LEFT)}}</td>
                                </tr>
                            </table>
                            <h3 style="margin: 0;color:#a8a8a8;font-style: italic; font-size: 13px;">FACTURAR A:</h3>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-column" style="width: 50px;">Nombre</th>
                        <td style="text-align: center;font-size: 12px; border-top: 2px solid black;border-left: 2px solid black;border-right: 2px solid black;">{{$quotation->customer->customer_name}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Dirección</th>
                        <td style="font-size: 12px;border-left: 2px solid black;border-right: 2px solid black;">{{$quotation->quotation_customer_address}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">RUC</th>
                        <td style="font-size: 12px;border-left: 2px solid black;border-right: 2px solid black;">{{$quotation->customer->customer_number_document}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Contacto</th>
                        <td style="font-size: 12px; font-weight: 500;border-left: 2px solid black;border-right: 2px solid black;">{{$quotation->contact->contact_name}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Email</th>
                        <td style="font-size: 12px;text-decoration: underline;color:#5c5cff;border-left: 2px solid black;border-right: 2px solid black;">{{$quotation->contact->contact_email}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Tlfno</th>
                        <td style="font-size: 12px;text-decoration: underline;color:#5c5cff;border-left: 2px solid black;border-right: 2px solid black; border-bottom: 2px solid black;">{{$quotation->contact->contact_number}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="font-size: 13px; margin-bottom: 10px;" class="table-details">
        <thead>
            <tr style="background-color: #cdcdcd;">
                <th style="width: 40px;">Item</th>
                <th style="width: 80px;">Imagen</th>
                <th>Descripción</th>
                <th style="width: 80px;">Cantidad</th>
                <th style="width: 100px;">Precio</th>
                <th style="width: 100px;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->products as $key => $detail)
            @php
                $price = $detail->pivot->detail_price_unit + $detail->pivot->detail_price_additional;
                $urlImage = empty($producto->producto->urlImagen) || !\File::exists($path) ? 'img/no-picture-taking.png' : $detail->product_img;
            @endphp
                <tr>
                    <td style="text-align: center;">{{$key + 1}}</td>
                    <td style="text-align: center; padding: 5px;">
                        
                        <img src="{{public_path($urlImage)}}" alt="Imagen de productos" width="90px" height="90px">
                    </td>
                    <td>{{$detail->product_name}}</td>
                    <td style="text-align: center;">{{$detail->pivot->detail_quantity}}</td>
                    <td style="text-align: center;">{{$money.number_format($price,2)}}</td>
                    <td style="text-align: center;">{{$money.number_format($detail->pivot->detail_total,2)}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">SUBTOTAL</th>
                <td>{{$money.number_format($quotation->quotation_amount,2)}}</td>
            </tr>
            @if ($quotation->quotation_discount > 0)
            <tr>
                <th colspan="5">DESCUENTO</th>
                <td>{{$money.number_format($quotation->quotation_discount,2)}}</td>
            </tr>
            @endif
            <tr>
                <th colspan="5">IGV 18%</th>
                <td>{{$money.number_format($quotation->quotation_igv,2)}}</td>
            </tr>
            <tr>
                <th colspan="5">TOTAL</th>
                <td>{{$money.number_format($quotation->quotation_total,2)}}</td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size: 14px; margin-bottom: 10px;">
        Insumos de origen europeo
    </p>
    @empty(!$quotation->quotation_description_products)
    <div style="margin-bottom: 10px;">
        <h2 style="font-size: 16px; margin-bottom: 0; margin-top: 0;">Descripción del los productos</h2>
        {!! $quotation->quotation_description_products !!}
    </div>
    @endempty
    <div>
        <h2 style="font-size: 16px; margin-bottom: 0; margin-top: 0;">Observaciones</h2>
        {!! $quotation->quotation_observations !!}
    </div>
    <div>
        <h2 style="font-size: 16px; margin-bottom: 0; margin-top: 0;">Condiciones</h2>
        {!! $quotation->quotation_conditions !!}
    </div>
    <table>
        <tr>
            <td style="vertical-align: top; width: 380px;">
                <strong style="font-size: 14px;">DATOS BANCARIOS</strong><br>
                {!! $configuration->where('description','=','business_bank')->first()->value !!}
            </td>
            <td style="vertical-align: top;">
                <p style="line-height: 2;font-size: 13px;">
                    <strong>Firma de Aceptación Presopuesto y Orden Compra:</strong><br>
                    <span>Nombre:</span><br>
                    <span>DNI:</span><br>
                </p>
            </td>
        </tr>
        <tr>
            @foreach ($pagesArray as $pag)
                <td style="text-align: center; font-size: 12px;">
                    {{$pag}}
                </td>
            @endforeach
        </tr>
    </table>
</body>
</html>