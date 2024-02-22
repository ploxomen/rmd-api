<table>
    <tr></tr>
    <tr>
        <td></td>
        <td colspan="3">LISTA DE PRODUCTOS</td>
    </tr>
    <tr></tr>
    @foreach ($categories as $categorie)
        <tr>
            <td></td>
            <td colspan="4">{{$categorie->categorie_name}}</td>
        </tr>
        @foreach ($categorie->subcategories()->where(['sub_categorie_status'=>1])->get() as $subcategorie)
            <tr>
                <td></td>
                <td colspan="4">{{$subcategorie->sub_categorie_name}}</td>
            </tr>
            <tr>
                <td></td>
                <th>NOMBRE</th>
                <th>DESCRIPCIÓN</th>
                <th>P. PRODUCCIÓN</th>
                <th>P. VENTA</th>
            </tr>
            @foreach ($subcategorie->products()->where(['product_status'=>1])->get() as $product)
                <tr>
                    <td></td>
                    <td>{{$product->product_name}}</td>
                    <td>{{$product->product_description}}</td>
                    <td>{{$product->product_buy}}</td>
                    <td>{{$product->product_sale}}</td>
                </tr>
            @endforeach
        @endforeach
    @endforeach
</table>