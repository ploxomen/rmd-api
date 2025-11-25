<table>
    <tr></tr>
    <tr>
        <td></td>
        <td colspan="15">{{ $title }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td></td>
        <td>N°</td>
        <td>FECHA</td>
        <td>ALMACEN</td>
        <td>TIPO MOV.</td>
        <td>NUM. DOC. PROV.</td>
        <td>PROVEEDOR</td>
        <td>ARTICULO</td>
        <td>N° GUIA</td>
        <td>DESCRIPCION</td>
        <td>CANTIDAD</td>
        <td>MONEDA</td>
        <td>TIPO CAMBIO</td>
        <td>COSTO UNITARIO</td>
        <td>COSTO TOTAL</td>
        <td>VALOR UNITARIO</td>
        <td>VALOR TOTAL</td>
    </tr>
    @foreach ($transactions as $key => $transaction)
        <tr>
            <td></td>
            <td>{{ $key + 1 }}</td>
            <td>{{ $transaction->date }}</td>
            <td>{{ $transaction->store }}</td>
            <td>{{ $transaction->type_mov }}</td>
            <td>{{ $transaction->number_doc_provider }}</td>
            <td>{{ $transaction->provider }}</td>
            <td>{{ $transaction->product_code }}</td>
            <td>{{ $transaction->number_guide }}</td>
            <td>{{ $transaction->product_name }}</td>
            <td>{{ $transaction->stock }}</td>
            <td>{{ $transaction->type_money }}</td>
            <td>{{ $transaction->type_change_money }}</td>
            <td>{{ $transaction->price_unit_pen }}</td>
            <td>{{ $transaction->cost_total_pen }}</td>
            <td>{{ $transaction->valorization_unit }}</td>
            <td>{{ $transaction->valorization_total }}</td>
        </tr>
    @endforeach
</table>