<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Cotización</title>
</head>
<body>
    @include('styles.pdfv2Style')
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
            <td style="vertical-align: top;">
                <img src={{public_path('img/logo-rmd.png')}} alt="imagen" width="182px">
            </td>
            <td style="vertical-align: top; text-align: right;">
                <img src={{public_path('img/rmd-list-description.png')}} width="245px">
                <img src={{public_path('img/rmd-header.jpg')}} alt="imagen" width="165px">
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
                            $numberProduct++;
                            $product = App\Models\Products::find($detail['id']);
                            $price = $detail['price_unit'] +  $detail['price_aditional'];
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
                                <div style="font-family:Helvetica,Arial,sans-serif; font-size:8pt;">
                                    {!!$detail['details']!!}
                                </div>
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
                <th style="width: 100%" colspan="2" class="title-extra color-primary-title">GARANTÍA</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions" style="width: 50%;">
                    {!! $quotation['quotation_warranty_1'] !!}
                </td>
                <td class="parragraph-conditions" style="width: 50%;">
                    {!! $quotation['quotation_warranty_2'] !!}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 12px; font-weight: bold;">
                    * Cualquier condición accesoria o complementaria que aplique a la garantía será especificada de manera expresa en la correspondiente carta de garantía emitida para cada caso particular.
                </td>
            </tr>
        </tbody>
    </table>
    <table class="table-extra">
        <thead>
            <tr>
                <th class="title-extra color-primary-title">FORMA DE PAGO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="parragraph-conditions" style="font-size: 11px;">{{$quotation['quotation_way_to_pay']}}</td>
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