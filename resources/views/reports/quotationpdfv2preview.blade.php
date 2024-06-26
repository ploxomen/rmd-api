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
        .imagen{
            display: block;
        }
        .title {
            display: block;
            padding: 6px;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
        }
        .color-primary-title{
            background:#424242;
            color: #ffff;
        }
        .color-secondary-title{
            background:#008A32;
            color: #ffff;
        }
        .color-terciario-title{
            background:#008a3356;
        }
        .parragraph-small{
            margin: 0;
            margin-bottom: 15px;
            font-size: 12px;
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
        .table-general{
            font-size: 12px;
            margin-bottom: 10px;
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
        .table-borders td{
            border-left: 1px solid #000000;
            border-bottom: 1px solid #000000;
            border-right: 1px solid #000000;
            border-top: 1px solid #000000;
        }
        .td-wdata{
            width: 150px;
            padding-left: 12px;
        }
        .td-wdata-client{
            font-weight: 500;
            padding-left: 12px;
        }
        .parragraph-conditions p{
            margin-bottom: 2px;
        }
        .table-extra{
            margin-bottom: 8px;
            page-break-inside:avoid;
        }
        .title-extra{
            font-size: 13px;
            text-align: left;
            font-weight: 500;
            padding: 4px 4px 4px 8px;
        }
        .text-email{
            color: #008A32;
        }
        .anulado{
            position: fixed;
            top: 50%;
            z-index: 5;
            left: 50%;
            font-size: 80px;
            font-weight: bold;
            transform: translate(-50%,-50%) rotate(-45deg);
            text-align: center;
            width: 100%;
            font-family: Arial, Helvetica, sans-serif;
            color: rgba(255, 0, 0, 0.234);
        }
    </style>
    @php
        $emails = $configuration->where('description','=','business_email')->first()->value;
        $phones = $configuration->where('description','=','business_cell_phone')->first()->value;
        $pages = $configuration->where('description','=','business_page')->first()->value;
        $pagesArray = explode('/',$pages);
        $listEmails = explode('/',$emails);
        $listPhones = implode(' / ',explode('/',$phones));
        $money = $quotation['quotation_type_money'] == 'PEN' ? 'S/' : '$';
        $customer = App\Models\Customers::find($quotation['quotation_customer']);
        $contact = App\Models\Contacts::find($quotation['quotation_contact']);
        $subtotal = 0;
        $numberProduct = 0;
    @endphp
    <div class="anulado">
        <span>VISTA PREVIA</span>
    </div>
    <table style="margin-bottom: 20px;">
        <tr>
            <td style="width: 280px;">
                <p style="font-size: 12px; color:#000000; line-height: 1.1;">
                    <span>{{$configuration->where('description','=','business_name')->first()->value}}</span><br>
                    <span>RUC: {{$configuration->where('description','=','business_ruc')->first()->value}}</span><br>
                    <span>{{$configuration->where('description','=','business_address')->first()->value}}</span><br>
                    <span>Teléfonos: {{ $listPhones }}</span><br>
                    <span>Web: {{isset($pagesArray[0]) ? $pagesArray[0] : ''}}</span><br>
                    <span>Fecha: {{date('d/m/Y',strtotime($quotation['quotation_date_issue']))}}</span><br>
                    <span>N°: --</span>
                </p>
            </td>
            <td>
                <img src={{public_path('img/logo.jpg')}} alt="imagen" width="120px">
            </td>
            <td style="vertical-align: top; text-align: right;">
                <img src={{public_path('img/rmd-list-description.png')}} style="margin-bottom: 12px;" width="300px">
                <img src={{public_path('img/rmd-header.jpg')}} alt="imagen" width="150px">
            </td>
        </tr>
    </table>
    <span class="title color-primary-title">PRESUPUESTO N° </span>
    <span class="title color-secondary-title">DATOS DE COTIZACIÓN</span>
    <table class="table-general table-borders">
        <tr>
            <td class="td-wdata">Cliente: </td>
            <td class="td-wdata-client">{{$customer ? $customer->customer_name : ''}}</td>
        </tr>
        <tr>
            <td class="td-wdata">RUC: </td>
            <td class="td-wdata-client">{{$customer ? $customer->customer_number_document : ''}}</td>
        </tr>
    </table>
    <table class="table-general table-borders">
        <tr>
            <td class="td-wdata">Proyecto: </td>
            <td class="td-wdata-client">{{$quotation['quotation_project']}}</td>
        </tr>
    </table>
    <table class="table-general table-borders">
        <tr>
            <td class="td-wdata">Contacto: </td>
            <td class="td-wdata-client">{{$contact ? $contact->contact_name : ''}}</td>
        </tr>
        <tr>
            <td class="td-wdata">Teléfono: </td>
            <td class="td-wdata-client">{{$contact ? $contact->contact_number : ''}}</td>
        </tr>
        <tr>
            <td class="td-wdata">Email: </td>
            <td class="td-wdata-client">{{$contact ? $contact->contact_email : ''}}</td>
        </tr>
    </table>
    <span class="title color-secondary-title">LISTA DE PRODUCTOS / SERVICIOS</span>
    <table style="font-size: 11px;margin-bottom: 10px;" class="table-details">
        <thead>
            <tr style="font-size: 11px; text-align: center;" class="color-primary-title">
                <th style="width: 40px;border-top:1px solid black;border-right:1px solid black;border-left:1px solid black">Item</th>
                <th style="width: 70px;border-top:1px solid black;border-right:1px solid black;">Imagen</th>
                <th style="border-top:1px solid black;border-right:1px solid black;">Descripción</th>
                <th style="width: 80px;border-top:1px solid black;border-right:1px solid black;">Cantidad</th>
                <th style="width: 100px;border-top:1px solid black;border-right:1px solid black;">Precio</th>
                <th style="width: 100px;border-top:1px solid black;border-right:1px solid black;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailsQuotation as $keyCategories => $quotationDetail)
                @foreach ($quotationDetail['subcategories'] as $keySubCategorie => $subcategories)
                    <tr class="color-terciario-title">
                        <th colspan="2" style="border-left: 1px solid #000000; border-bottom: 1px solid #000000;border-top: 1px solid #000000;"></th>
                        <th style="text-align: center;font-weight:bold; border-bottom: 1px solid #000000;border-top: 1px solid #000000;">
                            {{$quotationDetail['categorie_name'] . ' - ' . $subcategories['subcategorie_name']}}
                        </th>
                        <th colspan="3" style="border-right: 1px solid #000000; border-bottom: 1px solid #000000;border-top: 1px solid #000000;"></th>
                    </tr>
                    @foreach ($subcategories['products'] as $key => $detail)
                        @php
                            $price = $detail->pivot->detail_price_unit + $detail->pivot->detail_price_additional;
                            $pathImg = $detail->product_img;
                            $urlImage = empty($pathImg) || !\File::exists($pathImg) ? null : $pathImg;
                            $rowFinal = ($keyCategories + 1) === count($detailsQuotation) && ($keySubCategorie + 1) === count($quotationDetail['subcategories']) && ($key + 1) === count($subcategories['products']) ? 2 : 1;
                            $numberProduct++;
                        @endphp
                         <tr>
                            <td style="text-align: center;">{{$numberProduct}}</td>
                            <td style="text-align: center; padding: 5px;">
                                @empty(!$urlImage)
                                <img src="{{public_path($urlImage)}}" alt="Imagen de productos" width="60px" height="60px">
                                @endempty
                            </td>
                            <td style="padding-left: 5px; line-height:0.8;">
                                {{$detail->product_name}}
                                @empty(!$detail->pivot->quotation_description)
                                <br>{!!$detail->pivot->quotation_description!!}
                                @endempty
                            </td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$detail->pivot->detail_quantity}}</td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$money.number_format($price,2)}}</td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$money.number_format($detail->pivot->detail_total,2)}}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            <tr>
                <td colspan="3" class="color-terciario-title" style="padding-left: 5px; font-weight:500;border-top: 1px solid black; font-size: 10px;">
                    <span style="text-decoration: underline;">INSUMOS DE ORIGEN EUROPEO</span>
                </td>
            </tr>
        </tbody>
        @php
            $importe = $subtotal - $quotation['quotation_discount'];
            $igv = $quotation['quotation_include_igv'] === true ? $importe * 0.18 : 0;
            $total = $importe + $igv;
        @endphp
        <tfoot>
            <tr>
                <th colspan="5">SUBTOTAL</th>
                <td>{{$money.number_format($subtotal,2)}}</td>
            </tr>
            @if ($quotation['quotation_discount'] > 0)
            <tr>
                <th colspan="5">DESCUENTO</th>
                <td>{{$money.number_format($quotation['quotation_discount'],2)}}</td>
            </tr>
            @endif
            @if ($igv > 0)
            <tr>
                <th colspan="5">IGV 18%</th>
                <td>{{$money.number_format($igv,2)}}</td>
            </tr>
            @endif
            <tr>
                <th colspan="5">TOTAL</th>
                <td>{{$money.number_format($total,2)}}</td>
            </tr>
        </tfoot>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th style="width: 50%" class="title-extra color-primary-title">OBSERVACIONES</th>
                <th class="color-secondary-title title-extra">HORARIOS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions">
                    {!! $quotation['quotation_observations'] !!}
                </td>
                <td class="parragraph-conditions">
                    {!! $configuration->where('description','=','attention_hours')->first()->value !!}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th style="width: 50%" class="color-primary-title title-extra">CONDICIONES</th>
                <th class="color-secondary-title title-extra"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" class="parragraph-conditions">
                    {!! $quotation['quotation_conditions'] !!}
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th style="width: 50%" class="title-extra color-primary-title">GARANTÍA</th>
                <th class="color-secondary-title title-extra">FORMA DE PAGO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions">
                    <p style="white-space: pre-wrap; font-size: 11px;">{{$quotation['quotation_warranty']}}</p>
                </td>
                <td style="font-size: 11px; vertical-align: top;">{{$quotation['quotation_way_to_pay']}}</td>
            </tr>
        </tbody>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th style="width: 50%" class="title-extra color-primary-title">DATOS BANCARIOS</th>
                <th class="color-secondary-title title-extra">Firma de Aceptación Presupuesto y Orden Compra</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions">
                    {!! $configuration->where('description','=','business_bank')->first()->value !!}
                </td>
                <td>
                    <p style="line-height: 2;font-size: 13px;">
                        <span>Nombre:</span><br>
                        <span>DNI:</span><br>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th style="width: 50%" class="title-extra color-primary-title">CONTÁCTANOS</th>
                <th class="color-secondary-title title-extra"></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions" style="font-size: 12px;">
                    <span>Sitio Web:</span>
                    <ul>
                        @foreach ($pagesArray as $pag)
                            <li>{{$pag}}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    <table border="1" style="text-align: center;font-size: 12px; margin-top:12px;">
                        <tr>
                            <td>Asesor comercial <br> <span style="font-size: 16px;">{{auth()->user()->user_name . ' ' . auth()->user()->user_last_name}} </span></td>
                        </tr>
                        <tr>
                            <td>CEL: {{auth()->user()->user_cell_phone}} </td>
                        </tr>
                        <tr>
                            <td>
                                @foreach ($listEmails as $key => $email)
                                    <a class="text-email">{{$email}}</a>
                                    @if (($key + 1) < count($listEmails))
                                        <span style="padding: 2px 0;" class="text-email"> | </span>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>