<table>
    <tr></tr>
    <tr></tr>
    {{-- <tr>
        <td colspan="6">
            PARQUES INFANTILES, CIRCUITOS CANINOS,
            SUPERFICIES DE SEGURIDAD, MOBILIARIO URBANO
        </td>
    </tr>

    <tr>
        <td colspan="6"></td>
    </tr> --}}

    {{-- =====================================================
        TÍTULO
    ====================================================== --}}

    <tr>
        <td colspan="6">
            ORDEN DE PRODUCCIÓN Nº -
            {{ $order->order_production_code }}
        </td>
    </tr>

    {{-- =====================================================
        INFORMACIÓN GENERAL
    ====================================================== --}}

    <tr>
        <td colspan="6">
            INFORMACIÓN GENERAL
        </td>
    </tr>

    <tr>

        <td>CLIENTE</td>

        <td colspan="2">
            {{ $order->customer->customer_name }}
        </td>

        <td>N° ORDEN</td>

        <td colspan="2">
            {{ $order->order_production_code }}
        </td>

    </tr>

    <tr>

        <td>RUC</td>

        <td colspan="2">
            {{ $order->customer->customer_number_document }}
        </td>

        <td>N° PEDIDO REF</td>

        <td colspan="2">
            {{ $ordersDetails['order_code'] ?? '' }}
        </td>

    </tr>

    <tr>

        <td>PROYECTO</td>

        <td colspan="2">
            {{ $ordersDetails['order_project'] ?? '' }}
        </td>

        <td>FECHA EMISIÓN</td>

        <td colspan="2">
            <strong>
                {{ $order->order_produc_date_issue->format('d/m/Y') }}
            </strong>
        </td>

    </tr>

    <tr>

        <td>CONTACTO</td>

        <td colspan="2">
            {{ $ordersDetails['order_contact_name'] ?? '' }}
        </td>

        <td>FECHA ENTREGA</td>

        <td colspan="2">
            <strong>
                {{ $order->order_produc_date_delive->format('d/m/Y') }}
            </strong>
        </td>

    </tr>

    <tr>

        <td>TELÉFONO</td>

        <td colspan="2">
            {{ $ordersDetails['order_contact_telephone'] ?? '' }}
        </td>

        <td>
            CONDICIONES Y DIRECCIÓN DE ENTREGA
        </td>

        <td colspan="2">
            {{ $order->order_produc_address }}
        </td>

    </tr>

    {{-- =====================================================
        DETALLE
    ====================================================== --}}

    <tr>
        <td colspan="6">
            DETALLE DE PRODUCCIÓN
        </td>
    </tr>

    <tr>

        <th>COT.</th>

        <th>
            PRODUCTO / DESCRIPCIÓN.
        </th>

        <th>CANT.</th>

        <th>
            HR. TOT. ESCANDALLO.
        </th>

        <th>SECCIÓN</th>

        <th>
            HORAS SECCIÓN
        </th>

    </tr>

    {{-- =====================================================
        PRODUCTOS
    ====================================================== --}}

    @foreach ($details as $detail)
        @forelse ($detail->list_labels as $label)
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

                <td>
                    {{ $label->product_label_name }}
                </td>

                <td>
                    {{ $label->time_origin_hours }}
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

    {{-- =====================================================
        TOTAL
    ====================================================== --}}

    <tr>

        <td colspan="5">
            TOTAL HORAS PREVISTAS
        </td>

        <td>
            {{ $total }}
        </td>

    </tr>

    {{-- =====================================================
        OBSERVACIONES
    ====================================================== --}}

    <tr>

        <td colspan="6">
            OBSERVACIONES
        </td>

    </tr>

    <tr>

        <td colspan="6">
            {{ $order->order_production_detail }}
        </td>

    </tr>

    {{-- =====================================================
        FIRMAS
    ====================================================== --}}

    <tr>

        <td colspan="2">
            Responsable de Producción
        </td>

        <td></td>

        <td colspan="2">
            Responsable Arquitectura
        </td>

        <td></td>

    </tr>

</table>
