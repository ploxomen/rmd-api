<table>
        <tr></tr>
        <tr>
            <td></td>
            <td class="title" colspan="8">REPORTE DE CLIENTES</td>
        </tr>
        <tr></tr>
    </table>
    <table border="1">
        <thead>
            <tr>
                <th>CODIGO</th>
                <th>NOMBRE</th>
                <th>T. DOCU.</th>
                <th>N. DOCU.</th>
                <th>NOM. CONTAC.</th>
                <th>N. CONTAC.</th>
                <th>EMAIL CONTAC.</th>
                <th>CARGO CONTAC.</th>
                <th>DEP.</th>
                <th>PROV.</th>
                <th>DIST.</th>
                <th>DIREC.</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($customers as $customer)
            @foreach ($customer->contacts as $contact)
                <tr>
                    <td>{{ str_pad($customer->id, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ $customer->typeDocument->document_name }}</td>
                    <td>{{ $customer->customer_number_document }}</td>
                    <td>{{ $contact->contact_name }}</td>
                    <td>{{ $contact->contact_number }}</td>
                    <td>{{ $contact->contact_email }}</td>
                    <td>{{ $contact->contact_position }}</td>
                    <td>{{ $customer->district?->departament->departament_name }}</td>
                    <td>{{ $customer->district?->province->province_name }}</td>
                    <td>{{ $customer->district?->district_name }}</td>
                    <td>{{ $customer->customer_address }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>