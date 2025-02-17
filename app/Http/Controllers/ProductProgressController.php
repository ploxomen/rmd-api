<?php

namespace App\Http\Controllers;

use App\Models\ProductProgress;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

class ProductProgressController extends Controller
{
    private $urlModule = '/store/product-progress';
    public function index(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $show = $request->show;
        $search = $request->has('search') ? $request->search : '';
        $skip = ($request->page - 1) * $request->show;
        $productProgress = ProductProgress::getProductProgress($request->filter_initial,$request->filter_final,$search);
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Productos en progreso obtenidos correctamente',
            'total' => $productProgress->get()->count(),
            'data' => $productProgress->skip($skip)->take($show)->get()
        ]);
    }
    public function getRawMaterialActive(Request $request) {
        $redirect = (new AuthController)->userRestrict($request->user(),$this->urlModule);
        $rawMaterials = RawMaterial::getRawMasterials(null,'');
        return response()->json([
            'redirect' => $redirect,
            'error' => false,
            'message' => 'Materia prima obtenidos correctamente',
            'data' => $rawMaterials->get()
        ]);
    }
}
