<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Products;
use App\Models\SubCategories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoriesController extends Controller
{
    private $urlModule = '/categories';
    public function index(Request $request){
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $categories = Categories::where('categorie_status','>',0)->where('categorie_name','like','%'.$search.'%');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Categorías obtenidas correctamente',
            'totalCategories' => $categories->count(),
            'data' => $categories->select("categorie_name","id")->selectRaw("(select COUNT(*) from sub_categories WHERE categorie_id = categories.id AND sub_categorie_status > 0) AS subcategorie_quantity")->skip($skip)->take($show)->get()
        ]);
    }
    public function update(Request $request) {        
        $idCategorie = $request->categorie;
        $validator = Validator::make($request->all(),[
            'categorie_name' => ['required','string','max:250',Rule::unique('categories')->where(function($query)use($idCategorie){
             $query->where('categorie_status','!=',0)->where('id','!=',$idCategorie);   
            })
            ]
        ]);
        $validator->setAttributeNames([
            'categorie_name' => 'nombre de la categoría'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true,'redirect' => null, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()->all()]);
        }
        $categorie = Categories::find($idCategorie);
        $categorie->update([
            'categorie_name' => $request->categorie_name,
        ]);
        $subcategories = [];
        foreach ($request->subcategories as $subcategorie) {
            $subcategorieNew = [
                'sub_categorie_name' => $subcategorie['sub_categorie_name'],
            ];
            if($subcategorie['type'] == 'new'){
                $subcategorieNew['sub_categorie_status'] = 1;
                $subcategories[] = $subcategorieNew;
            }else{
                SubCategories::where(['categorie_id' => $idCategorie,'id' => $subcategorie['id']])->update($subcategorieNew);
            }
        }
        $categorie->subcategories()->createMany($subcategories);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Categoría modificada correctamente', 
        ]);
    }
    public function show(Request $request) {        
        $categorie = Categories::find($request->categorie)->makeHidden(['created_at','updated_at','categorie_status']);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Cliente obtenido correctamente', 
            'data' => [
                'categorie' => $categorie,
                'subcategories' => $categorie->subcategories()->select('sub_categorie_name','id')->selectRaw("'old' AS type")->where('sub_categorie_status','>',0)->get()
            ]
        ]);
    }
    public function destroy(Request $request) {
        Categories::find($request->categorie)->update(['categorie_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Categoría eliminada correctamente',
        ]);
    }
    public function deleteSubcategorie(Request $request) {
        if(Products::where('sub_categorie',$request->subcategorie)->count() > 0){
            return response()->json([
                'error' => true,
                'message' => 'No se puede eliminar la subcategoría porque está presente en uno o varios productos',
            ]);
        }
        SubCategories::find($request->subcategorie)->update(['sub_categorie_status' => 0]);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Subcategoría eliminada correctamente',
        ]);
    }
    public function store(Request $request) {        
        $validator = Validator::make($request->all(),[
            'categorie_name' => ['required','string','max:250',Rule::unique('categories')->where(function($query){
             $query->where('categorie_status','!=',0);   
            })
            ]
        ]);
        $validator->setAttributeNames([
            'categorie_name' => 'nombre de la categoría'
        ]);
        if($validator->fails()){
            return response()->json(['error' => true, 'message'=>'Los campos no estan llenados correctamentes','data' => $validator->errors()]);
        }
        $categorie = Categories::create([
            'categorie_name' => $request->categorie_name,
            'categorie_status' => 1
        ]);
        $subcategories = [];
        foreach ($request->subcategories as $subcategorie) {
            $subcategories[] = [
                'sub_categorie_name' => $subcategorie['sub_categorie_name'],
                'sub_categorie_status' => 1
            ];
        }
        $categorie->subcategories()->createMany($subcategories);
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Categoría creada correctamente', 
            'data' => $categorie,
        ]);
    }
}
