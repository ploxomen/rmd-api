<table>
    <tr></tr>
    <tr>
        <td></td>
        <td colspan="9">REPORTE DE COMPRAS</td>
    </tr>
    <tr></tr>
    <tr>
        <td></td>
        <td>FECHA</td>
        <th>PROVEEDOR</th>
        <th>RUC/N° DOCUMENTO</th>
        <th>TIPO DOC.</th>
        <th>REF. PROVEEDOR</th>
        <th>REFERENCIA SECUNDARIA</th>
        <th>USUARIO</th>
        <th>T/C</th>
        <th>NETO SOLES</th>
    </tr>
    @php
        $total = 0;
    @endphp
    @foreach ($shopping as $buy)
        @php
            $total += $buy->buy_total;
            $importedData = [];
            if (!empty($buy->imported_expenses_cost)) {
                $importedData[] = (object) [
                    'references_second' => 'GASTOS DE ORIGEN',
                    'value' => $buy->imported_expenses_cost,
                ];
            }
            if (!empty($buy->imported_flete_cost)) {
                $importedData[] = (object) [
                    'references_second' => 'FLETE',
                    'value' => $buy->imported_flete_cost,
                ];
            }
            if (!empty($buy->imported_insurance_cost)) {
                $importedData[] = (object) [
                    'references_second' => 'SEGURO',
                    'value' => $buy->imported_insurance_cost,
                ];
            }
            if (!empty($buy->imported_destination_cost)) {
                $importedData[] = (object) [
                    'references_second' => 'GASTOS DE DESTINO',
                    'value' => $buy->imported_destination_cost,
                ];
            }
        @endphp
        <tr>
            <td></td>
            <td>{{ $buy->buy_date_invoice }}</td>
            <td>{{ $buy->provider_name }}</td>
            <td>{{ $buy->provider_number_document }}</td>
            <td>FA</td>
            <td>{{ $buy->buy_number_invoice }}</td>
            <td>FACTURA COMERCIAL</td>
            <td>{{ $buy->user_name }} {{ $buy->user_last_name }}</td>
            <td>{{ $buy->buy_type_change }}</td>
            <td>{{ $buy->buy_total }}</td>
        </tr>
        @foreach ($importedData as $import)
            @php
                $total += $import->value * $buy->buy_type_change;
            @endphp
            <tr>
                <td></td>
                <td>{{ $buy->buy_date_invoice }}</td>
                <td>{{ $buy->provider_name }}</td>
                <td>{{ $buy->provider_number_document }}</td>
                <td>CN</td>
                <td>{{ $buy->buy_number_invoice }}</td>
                <td>{{ $import->references_second }}</td>
                <td>{{ $buy->user_name }} {{ $buy->user_last_name }}</td>
                <td>{{ $buy->buy_type_change }}</td>
                <td>{{ $import->value * $buy->buy_type_change }}</td>
            </tr>
        @endforeach
    @endforeach
    <tr>
        <td></td>
        <td></td>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th>{{ $total }}</th>
    </tr>
</table>
