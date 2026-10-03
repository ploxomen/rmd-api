<table>
    <tr></tr>
    <tr></tr>
    <tr>
        <td colspan="8">
            ORDEN DE PRODUCCIÓN Nº -
            {{ $order->order_production_code }}
        </td>
    </tr>
    <tr>
        <td colspan="8">
            INFORMACIÓN GENERAL
        </td>
    </tr>
    <tr>
        <td>CLIENTE</td>
        <td colspan="3">
            {{ $order->customer->customer_name }}
        </td>
        <td>N° ORDEN</td>
        <td colspan="3">
            {{ $order->order_production_code }}
        </td>
    </tr>

    <tr>

        <td>RUC</td>
        <td colspan="3">
            {{ $order->customer->customer_number_document }}
        </td>
        <td>N° PEDIDO REF</td>
        <td colspan="3">
            {{ $ordersDetails['order_code'] ?? '' }}
        </td>
    </tr>

    <tr>
        <td>PROYECTO</td>
        <td colspan="3">
            {{ $ordersDetails['order_project'] ?? '' }}
        </td>
        <td>FECHA EMISIÓN</td>
        <td colspan="3">
            <strong>
                {{ $order->order_produc_date_issue->format('d/m/Y') }}
            </strong>
        </td>

    </tr>

    <tr>
        <td>CONTACTO</td>
        <td colspan="3">
            {{ $ordersDetails['order_contact_name'] ?? '' }}
        </td>
        <td>FECHA ENTREGA</td>
        <td colspan="3">
            <strong>
                {{ $order->order_produc_date_delive->format('d/m/Y') }}
            </strong>
        </td>

    </tr>
    <tr>
        <td>TELÉFONO</td>
        <td colspan="3">
            {{ $ordersDetails['order_contact_telephone'] ?? '' }}
        </td>
        <td>
            CONDICIONES Y DIRECCIÓN DE ENTREGA
        </td>
        <td colspan="3">
            {{ $order->order_produc_address }}
        </td>
    </tr>
    <tr>
        <td colspan="8">
            DETALLE DE PRODUCCIÓN
        </td>
    </tr>

    <tr>
        <th>IMAGEN REF.</th>
        <th>
            PRODUCTO/DESCRIPCIÓN.
        </th>
        <th>CANT.</th>
        <th>
            UND.
        </th>

        <th>HR. TOT. ESCANDALLO</th>
        <th>
            ÁREAS
        </th>
        <th>HH/UND.</th>
        <th>TOTAL HH</th>
    </tr>
    @foreach ($details as $detail)
        @forelse ($detail->list_labels as $label)
            <tr>
                <td>

                </td>
                <td>
                    {{ $detail->product_name }}
                @empty(!$detail->product_description)
                    {!! $detail->product_description !!}
                @endempty
            </td>
            <td>
                {{ $detail->amount }}
            </td>
            <td>
                Unidad
            </td>
            <td>
                {{ $label->pro_escandallo_total }} HH
            </td>
            <td>
                {{ $label->product_label_name }}
            </td>
            <td>
                {{ $label->time_origin_hours }} HH
            </td>
            <td>
                {{ $detail->subtotal }} HH
            </td>
        </tr>
    @empty
        <tr>
            <td>
                {{ $detail->order_code }}
            </td>
            <td>
                {{ $detail->product_name }}
            </td>
            <td>
                {{ $detail->amount }}
            </td>
            <td>
                {{ $detail->subtotal }}
            </td>
            <td></td>
            <td></td>
        </tr>
    @endforelse
@endforeach
<tr>

    <td colspan="6">
        TOTAL HORAS PREVISTAS
    </td>
    <td colspan="2">
        {{ $total }}
    </td>
</tr>
<tr>
    <td colspan="8">
        OBSERVACIONES
    </td>
</tr>
<tr>
    <td colspan="8">
        {{ $order->order_production_detail }}
    </td>
</tr>
<tr>
    <td colspan="2">
        Responsable de Producción
    </td>
    <td></td>
    <td></td>
    <td></td>
    <td colspan="2">
        Responsable Arquitectura
    </td>
    <td></td>
</tr>
<tr></tr>
<tr></tr>
<tr>
    <td colspan="2">
        COMERCIAL :
    </td>
    <td></td>
    <td></td>
    <td></td>
    <td colspan="2">
        {{ $userName }}
    </td>
    <td></td>
</tr>
</table>
