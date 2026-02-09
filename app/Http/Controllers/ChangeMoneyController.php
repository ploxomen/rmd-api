<?php

namespace App\Http\Controllers;

use App\Models\ChangeMoney;
use Illuminate\Http\Request;

class ChangeMoneyController extends Controller
{

    public function index(Request $request)
    {
        $start = ($request->page - 1) * $request->show;
        $end = $request->show;
        $dateInitial = $request->input('date_initial', null);
        $dateFinaly = $request->input('date_finaly', null);
        $changeMoney = ChangeMoney::select('change_soles', 'id', 'change_day', 'change_attempts');
        if (!empty($dateFinaly) && !empty($dateInitial)) {
            $changeMoney->whereBetween('change_day', [$dateInitial, $dateFinaly]);
        }
        $changeMoney->orderByDesc('change_day');
        return response()->json([
            'redirect' => null,
            'error' => false,
            'total' => ChangeMoney::get()->count(),
            'data' => $changeMoney->skip($start)->take($end)->get()
        ]);
    }
    public function show($change)
    {
        $day = $change == "today" ? date('Y-m-d') : $change;
        return response()->json([
            'redirect' => null,
            'error' => false,
            'value' => ChangeMoney::select('change_soles', 'change_attempts', 'id', 'change_day')->where('change_day', $day)->first()
        ]);
    }
    public function update(Request $request, $change)
    {
        return $this->changeValueMoney($change, $request);
    }
    public function delete($change)
    {
        ChangeMoney::where('change_day', $change)->delete();
        return response()->json([
            'redirect' => null,
            'error' => false,
            'alert' => 'Tipo de cambio eliminado correctamente'
        ]);
    }
    public function store(Request $request)
    {
        $date = $request->input('change_day', date('Y-m-d'));
        return $this->changeValueMoney($date, $request);
    }
    public function changeValueMoney(string $date, Request $request)
    {
        $money = ChangeMoney::select('change_soles', 'change_attempts')->where('change_day', $date)->first();
        if (!empty($money) && $money->change_attempts === 2) {
            return response()->json([
                'redirect' => null,
                'error' => false,
                'alert' => 'Solo se adminten 2 cambios como máximo'
            ]);
        }
        $money = ChangeMoney::updateOrCreate([
            'change_day' => $date
        ], [
            'change_soles' => $request->change_soles,
            'change_user' => $request->user()->id,
            'change_attempts' => empty($money) ? 1 : 2
        ]);
        return response()->json([
            'redirect' => null,
            'error' => false,
            'message' => 'Tipo de cambios actualizado correctamente',
            'data' => $money
        ]);
    }
}
