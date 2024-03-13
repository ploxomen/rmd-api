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
        }rgba(255, 84, 84, 0.359)
    </style>
    @php
        $emails = $configuration->where('description','=','business_email')->first()->value;
        $phones = $configuration->where('description','=','business_cell_phone')->first()->value;
        $pages = $configuration->where('description','=','business_page')->first()->value;
        $pagesArray = explode('/',$pages);
        $listEmails = implode('<br>',explode('/',$emails));
        $listPhones = implode(' / CEL: ',explode('/',$phones));
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
            <td style="width: 420px;">
                <img src={{public_path('img/rmd-header.jpg')}} alt="imagen" width="300px">
                <h1 class="title">{{$configuration->where('description','=','business_name')->first()->value}}</h1>
                <strong class="title">RUC: {{$configuration->where('description','=','business_ruc')->first()->value}}</strong>
                <p class="parragraph-small">{{$configuration->where('description','=','business_address')->first()->value}}</´p>
                <p class="parragraph-small">
                    CEL: {!! $listPhones !!}
                </p>
                <p style="margin-top:0; font-size:12px; text-decoration: underline; color:#5c5cff;">
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
                                    <td style="font-size: 12px;">{{date('d/m/Y',strtotime($quotation['quotation_date_issue']))}}</td>
                                </tr>
                                <tr>
                                    <th class="text-column">N° de presupuesto</th>
                                    <td style="font-size: 12px;"></td>
                                </tr>
                            </table>
                            <h3 style="margin: 0;color:#a8a8a8;font-style: italic; font-size: 13px;">FACTURAR A:</h3>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-column" style="width: 50px;">Nombre</th>
                        <td style="text-align: center;font-size: 12px; border-top: 2px solid black;border-left: 2px solid black;border-right: 2px solid black;">{{$customer ? $customer->customer_name : ''}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Proyecto</th>
                        <td style="font-size: 12px; text-align: center; border-left: 2px solid black;border-right: 2px solid black;">{{$quotation['quotation_project']}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">RUC</th>
                        <td style="font-size: 12px;border-left: 2px solid black;border-right: 2px solid black;">{{$customer ? $customer->customer_number_document : ''}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Contacto</th>
                        <td style="font-size: 12px; font-weight: 500;border-left: 2px solid black;border-right: 2px solid black;">{{$contact ? $contact->contact_name : ''}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Email</th>
                        <td style="font-size: 12px;text-decoration: underline;color:#5c5cff;border-left: 2px solid black;border-right: 2px solid black;">{{$contact ? $contact->contact_email : ''}}</td>
                    </tr>
                    <tr>
                        <th class="text-column">Tlfno</th>
                        <td style="font-size: 12px;text-decoration: underline;color:#5c5cff;border-left: 2px solid black;border-right: 2px solid black; border-bottom: 2px solid black;">{{$contact ? $contact->contact_number : ''}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="font-size: 11px;margin-bottom: 10px;" class="table-details">
        <thead>
            <tr style="font-size: 11px; background-color: #F2F2F2; text-align: center;">
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
                    <tr style="background-color: #C6E0B3;border:1px solid black;">
                        <td colspan="6" style="text-align: center;">
                            {{$quotationDetail['categorie_name'] . ' - ' . $subcategories['subcategorie_name']}}
                        </td>
                    </tr>
                    @foreach ($subcategories['products'] as $key => $detail)
                        @php
                            $numberProduct++;
                            $product = App\Models\Products::find($detail['id']);
                            $price = $detail['price_unit'] + $detail['price_aditional'];
                            $pathImg = $product->product_img;
                            $urlImage = empty($pathImg) || !\File::exists($pathImg) ? null : $pathImg;
                            $rowFinal = ($keyCategories + 1) === count($detailsQuotation) && ($keySubCategorie + 1) === count($quotationDetail['subcategories']) && ($key + 1) === count($subcategories['products']) ? 2 : 1;
                            $totalDetail = $price * $detail['quantity'];
                            $subtotal += $totalDetail;
                        @endphp
                         <tr>
                            <td style="text-align: center;">{{$numberProduct}}</td>
                            <td style="text-align: center; padding: 5px;">
                                @empty(!$urlImage)
                                <img src="{{public_path($urlImage)}}" alt="Imagen de productos" width="60px" height="60px">
                                @endempty
                            </td>
                            <td style="padding-left: 5px; line-height:0.8;">
                                {{$detail['description']}}
                                @empty(!$detail['details'])
                                <br>{!!$detail['details']!!}
                                @endempty
                            </td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$detail['quantity']}}</td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$money.number_format($price,2)}}</td>
                            <td style="text-align: center;" rowspan="{{$rowFinal}}">{{$money.number_format($totalDetail,2)}}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            <tr>
                <td colspan="3" style="background-color: #C6E0B3; padding-left: 5px; font-weight:bold;border-top: 1px solid black;">
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
    <div style="background-color: #F2F2F2;">
        {!! $quotation['quotation_observations'] !!}
        {!! $quotation['quotation_conditions'] !!}
    </div>
    <table>
        <tr>
            <td style="vertical-align: top; width: 360px; padding-right: 20px;">
                <span style="font-size: 14px; padding: 5px; background-color: #BFBFBF; display: block;">DATOS BANCARIOS</span>
                {!! $configuration->where('description','=','business_bank')->first()->value !!}
            </td>
            <td style="vertical-align: top; background-color: #F2F2F2; padding: 0 10px;">
                <p style="line-height: 2;font-size: 13px;">
                    <strong>Firma de Aceptación Presupuesto y Orden Compra:</strong><br>
                    <span>Nombre:</span><br>
                    <span>DNI:</span><br>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>