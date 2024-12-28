<?php

namespace App\Http\Controllers;

use App\Models\Stores;
use App\Models\StoresSub;
use Illuminate\Http\Request;

class StoresController extends Controller
{
    private $urlModule = '/store';
    public function getStoresAndSubStoresSelect() {
        $stores = Stores::where(['store_status' => 1])->get()->map(function ($store) {
            return [
                'label' => $store->store_name,
                'options' => $store->subStories()->select('id AS value','store_sub_name AS label')->get()->toArray() 
            ];
        })->toArray();
        return response()->json([
            'error' => false,
            'message' => 'Almacenes y subalmacenes obtenidos correctamente',
            'data' => $stores
        ]);
    }
    public function show(Request $request, Stores $store) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Producto obtenido correctamente', 
            'data' => [
                'store' => $store->where('store_status',1)->get()->makeHidden(['store_status','created_at','updated_at']),
                'subStores' => $store->subStories()->select('store_sub_name AS value','store_sub_name AS label', 'id AS idSubStore')->where('store_sub_status',1)->get(),
            ]
        ]);
    }
    public function destroy(Request $request, Stores $store) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        foreach ($store->subStories as $subStore) {
            if($subStore->products->count()){
                return response()->json([
                    'redirect' => null,
                    'error' => true, 
                    'message' => 'El almacen no puede ser eliminado ya que contiene productos asociados', 
                ]);
                break;
            }
        }
        $store->update([
            'store_status' => 0
        ]);
        $store->subStories()->update([
            'store_sub_status' => 0
        ]);
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Almacen eliminado correctamente', 
        ]);
    }
    public function update(Request $request, Stores $store) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $store->update([
            'store_name' => $request->store_name,
            'store_description' => $request->store_description
        ]);
        $store->subStories()->update([
            'store_sub_status' => 0
        ]);
        $subStrores = json_decode($request->listSubStore);
        foreach ($subStrores as $subStore) {
            if(isset($subStore->idSubStore)){
                $store->subStories()->where('id',$subStore->idSubStore)->update([
                    'store_sub_name' => $subStore->value,
                    'store_sub_status' => 1
                ]);
                continue;
            }
            StoresSub::create([
                'store_id' => $store->id,
                'store_sub_name' => $subStore->value
            ]);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Almacen actualizado correctamente', 
        ]);
    }
    public function store(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $store = Stores::create([
            'store_name' => $request->store_name,
            'store_description' => $request->store_description
        ]);
        $subStrores = json_decode($request->listSubStore);
        foreach ($subStrores as $subStore) {
            StoresSub::create([
                'store_id' => $store->id,
                'store_sub_name' => $subStore->value
            ]);
        }
        return response()->json([
            'redirect' => $redirect,
            'error' => false, 
            'message' => 'Almacen creado correctamente', 
        ]);
    }
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $stores = Stores::getStoresAndSubStores($search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Almacenes obtenidos',
            'total' => $stores->count(),
            'data' => $stores->skip($skip)->take($show)->get()
        ]);
    }
}
