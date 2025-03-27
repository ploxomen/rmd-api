<?php

namespace App\Http\Controllers;

use App\Models\ProductFinaly;
use App\Models\ProductFinalyAssembled;
use App\Models\ProductFinalyImported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductFinaliesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $urlModule = '/store/products-finaly';

    public function index(Request $request)
    {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $productsFinaly = ProductFinaly::select("product_name","products.id","product_code","product_label","product_finaly_stock","product_unit_measurement","product_public_customer","product_finalies.id AS product_finaly_id")->productsActive($search);
        if($request->filled('tipo')){
            $productsFinaly->where('product_label',$request->tipo);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos terminados obtenidos correctamente',
            'total' => $productsFinaly->get()->count(),
            'data' => $productsFinaly->skip($skip)->take($show)->get()
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $productFinaly = ProductFinaly::select("product_finalies.id","product_id")->joinProducts()->productExist()->where('product_id',$request->product_id)->first();
        
        if(empty($productFinaly)){
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'No se encontró el producto final'
            ],422);
        }
        if($productFinaly->products->product_store !== "PRODUCTO TERMINADO"){
            return response()->json([
                'redirect' => null,
                'error' => true,
                'message' => 'El producto seleccionado no es un producto terminado'
            ],422);
        }
        if($productFinaly->products->product_label === "IMPORTADO"){
            $request->validate([
                'product_finaly_provider' => 'required'
            ],[],['product_finaly_provider' => 'proveedor']);
        }else if($productFinaly->products->product_label === "ENSAMBLADO"){
            $request->validate([
                'details' => 'required|array'
            ],[],['details' => 'detalles']);
        }
        DB::beginTransaction();
        try {
            if($productFinaly->products->product_label === "IMPORTADO"){
                $productImported = new ProductFinalyImported();
                $productImported->product_finaly_id = $productFinaly->id;
                $productImported->fill($request->except('product_finaly_id','product_name','product_id'));
                $productImported->product_finaly_user = $request->user()->id;
                $productImported->save();
            }else if($productFinaly->products->product_label === "ENSAMBLADO"){
                $productAssembled = new ProductFinalyAssembled();
                $productAssembled->product_finaly_id = $productFinaly->id;
                $productAssembled->product_finaly_created = now()->toDateString();
                $productAssembled->product_finaly_amount = $request->product_finaly_amount;
                $productAssembled->product_finaly_description = $request->product_finaly_description;
                $productAssembled->product_finaly_user = $request->user()->id;
                $productAssembled->save();
                foreach ($request->details as $product) {
                    $productAssembled->product()->attach($product['detail_product_id'],['product_finaly_stock' => $product['detail_stock'],'product_finaly_type' => $product['detail_store']]);
                }
            }
            DB::commit();
            return response()->json(['redirect' => null, 'success' => true, 'message' => 'Historial generado correctamente']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage(),'error'=>true]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function totalStock(ProductFinaly $productFinaly){
        
    }
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
