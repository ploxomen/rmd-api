<table>
    <tr>
        <th>N° COTIZACIÓN</th>
        <th>FECHA</th>
        <th>VENDEDOR</th>
        <th>RAZON SOCIAL</th>
        <th>PAIS</th>
        <th>DEPARTAMENTO</th>
        <th>ESTADO COTIZACIÓN</th>
        <th>LINEA/ITEM</th>
        <th>PRODUCTO</th>
        <th>CANTIDAD PRODUCTO</th>
        <th>PRECIO UNITARIO(SOLO SOLES)</th>
        <th>CANTIDAD X PRECIO</th>
    </tr>
    @foreach ($quotations as $quotation)
        @php
            switch ($quotation->quotation_status) {
                case 0:
                    $status = "Anulado";
                break;
                case 1:
                    $status = "Generado";
                break;
                case 2:
                    $status = "En pedido";
                break;
                default:
                    $status = "No definido";
                break;
            }
        @endphp
        <tr>
            <td>{{$quotation->quotation_code}}</td>
            <td>{{$quotation->quotation_date_issue}}</td>
            <td>{{$quotation->user_name . ' ' . $quotation->user_last_name}}</td>
            <td>{{$quotation->customer_name}}</td>
            <td>{{$quotation->contrie}}</td>
            <td>{{$quotation->departament_name}}</td>
            <td>{{$status}}</td>
            @foreach ($quotation->products as $key => $detail)
                @php
                    $priceImport = $detail->pivot->detail_price_unit + $detail->pivot->detail_price_additional;
                    $priceImportConvert = $quotation->quotation_type_money != 'PEN' ? $quotation->quotation_change_money * $priceImport : $priceImport;
                    $totalDetail = $detail->pivot->detail_quantity * $priceImportConvert;
                @endphp
                @if ($key !== 0)
                    </tr>
                    <tr>
                    @for ($i = 0; $i < 7; $i++)
                        <td></td>
                    @endfor
                @endif
                <td>{{$key + 1}}</td>
                <td>{{$detail->product_name}}</td>
                <td>{{$detail->pivot->detail_quantity}}</td>
                <td>{{floatval($priceImportConvert)}}</td>
                <td>{{floatval($totalDetail)}}</td>
            @endforeach
        </tr>
    @endforeach
</table>