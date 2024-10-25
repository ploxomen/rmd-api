<table>
    <tr></tr>
    <tr>
        <td></td>
        <td colspan="6">LISTA DE PRODUCTOS</td>
    </tr>
    <tr></tr>
    <tr>
        <td></td>
        <td>CODIGO PRODUCTO</td>
        <th>NOMBRE PRODUCTO</th>
        <th>CATEGORÍA</th>
        <th>SUBCATEGORIA</th>
        <th>P. PRODUCCIÓN</th>
        <th>P. CLIENTE</th>
        <th>P. DISTRIBUIDOR</th>
    </tr>
    @foreach ($products as $product)
        <tr>
            <td></td>
            <td>{{ str_pad($product['product_code'],3,'0',STR_PAD_LEFT)}}</td>
            <td>{{$product['product_name']}}</td>
            <td>{{$product['category_name']}}</td>
            <td>{{$product['subcategory_name']}}</td>
            <td>{{$product['product_buy']}}</td>
            <td>{{$product['product_public_customer']}}</td>
            <td>{{$product['product_distributor']}}</td>
        </tr>
        
    @endforeach
</table>