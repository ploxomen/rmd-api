<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Models\Categories;
use App\Models\Products;
use App\Models\SubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    private $urlModule = '/products';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $products = Products::getProducts($search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos obtenidos correctamente',
            'totalProducts' => $products->count(),
            'data' => $products->skip($skip)->take($show)->get()
        ]);
    }
    public function exportProductsExcel(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect)){
            return response('Acceso no autorizado',403);
        }
        return Excel::download(new ProductsExport(Categories::where('categorie_status',1)->get(),'reports.products'),'products.xlsx');
    }
    public function categorie() {
        return response()->json([
            'error' => false,
            'message' => 'Categorias obtenidas correctamente',
            'data' => Categories::select('id','categorie_name')->where('categorie_status',1)->orderBy('categorie_name')->get()
        ]);
    }
    public function subcategorie($categorie) {
        return response()->json([
            'error' => false,
            'message' => 'Categorias obtenidas correctamente',
            'data' => SubCategories::select('id','sub_categorie_name')->where(['categorie_id' => $categorie,'sub_categorie_status'=>1])->orderBy('sub_categorie_name')->get()
        ]);
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'product_name' => ['required','string','max:250',Rule::unique('products')->where(function($query){
                $query->where('product_status','!=',0);   
               })
            ],
            'product_description' => 'nullable|string|max:2000',
            'product_buy' => 'nullable|decimal:0,2|min:0',
            'product_sale' => 'required|decimal:0,2|min:0',
            'sub_categorie' => 'required|numeric',
            'product_img' => 'nullable|image'
        ]);
        $validator->setAttributeNames([
            'product_name' => 'nombre del producto',
            'product_description' => 'descripción del producto',
            'product_buy' => 'precio de producción',
            'product_sale' => 'precio de venta',
            'sub_categorie' => 'subcategoría',
            'product_img' => 'imagen del producto'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all(),'redirect' => null]);
        }
        try {
            $dataProduct = $request->all();
            
            if($request->has('product_img')){
                $file = $request->file('product_img');
                $fileName = time() . "_" . $file->getClientOriginalName();
                $filePath = $file->storeAs('products',$fileName,'public');
                $dataProduct['product_img'] = 'storage/'.$filePath;
            }
            $dataProduct['product_status'] = 1;
            $product = Products::create($dataProduct);
            $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
            return response()->json([
                'redirect' => $redirect,
                'error' => false, 
                'message' => 'Producto creado correctamente', 
                'data' => $product,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'redirect' => null,
                'error' => true, 
                'message' => $th->getMessage(),
                'data' => [$th->getMessage()]
            ],500);
        }
    }
    public function update(Request $request,$product) {
        $dataProduct = $request->except("id","product_img","product_categorie");
        $validator = Validator::make($dataProduct,[
            'product_name' => ['required','string','max:250',Rule::unique('products')->where(function($query)use($product){
                $query->where('product_status','!=',0)->where('id','!=',$product);   
               })
            ],
            'product_description' => 'nullable|string|max:2000',
            'product_buy' => 'nullable|decimal:0,2|min:0',
            'product_sale' => 'required|decimal:0,2|min:0',
            'sub_categorie' => 'required|numeric',
            'product_img' => 'nullable|image'
        ]);
        $validator->setAttributeNames([
            'product_name' => 'nombre del producto',
            'product_description' => 'descripción del producto',
            'product_buy' => 'precio de producción',
            'product_sale' => 'precio de venta',
            'sub_categorie' => 'subcategoría',
            'product_img' => 'imagen del producto'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all(),'redirect' => null]);
        }
        try {
            $product = Products::find($product);
            if($request->has('delete_img') && File::exists($product->product_img)){
                File::delete($product->product_img);
                $dataProduct['product_img'] = null;
            }
            if($request->has('product_img')){
                $file = $request->file('product_img');
                $fileName = time() . "_" . $file->getClientOriginalName();
                $filePath = $file->storeAs('products',$fileName,'public');
                $dataProduct['product_img'] = 'storage/'.$filePath;
            }
            $product->update($dataProduct);
            $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
            return response()->json([
                'redirect' => $redirect,
                'error' => false, 
                'message' => 'Producto actualizado correctamente', 
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'redirect' => null,
                'error' => true, 
                'message' => $th->getMessage(),
                'data' => [$th->getMessage()]
            ],500);
        }
    }
    public function show(Request $request) {        
        $product = Products::find($request->product)->makeHidden(['created_at','updated_at','product_status','subcategorie']);
        $productCategorie = $product->subcategorie->categorie_id;
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Producto obtenido correctamente', 
            'data' => [
                'url' => $request->root(),
                'product' => $product,
                'categorieId' => $productCategorie,
                'subcategories' => SubCategories::select('id','sub_categorie_name')->where(['sub_categorie_status' => 1, 'categorie_id' => $productCategorie])->get()
            ]
        ]);
    }
    public function destroy(Request $request,$product) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        if(!is_null($redirect)){
            return response()->json([
                'redirect' => $redirect,
                'error' => false, 
            ],403);
        }
        Products::find($product)->update(['product_status' => 0]);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Producto eliminado correctamente'
        ]);
    }
}
