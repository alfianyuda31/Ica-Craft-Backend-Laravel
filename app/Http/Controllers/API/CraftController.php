<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Craft;
use Illuminate\Http\Request;

class CraftController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $types = $request->input('types');
        
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        
        $rate_from = $request->input('rate_from');
        $rate_to = $request->input('rate_to');
        
        if ($id) 
        {
            $craft = Craft::find('$id');
            
            if ($craft) 
            {
                return ResponseFormatter::success(
                    $craft,
                    'Data produk berhasil diambil'
                );
            }
            else
            {
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
            }
        }

        $craft = Craft::query();

        if ($name) 
        {
            $craft->where('name','like','%' . $name . '%');
        }

        if ($types) 
        {
            $craft->where('name','like','%' . $types . '%');
        }

        if ($price_from) 
        {
            $craft->where('price','>=',$price_from);
        }
        
        if ($price_to) 
        {
            $craft->where('price','<=',$price_to);
        }
        
        if ($rate_from) 
        {
            $craft->where('rate','>=',$rate_from);
        }
        
        if ($rate_to) 
        {
            $craft->where('rate','<=',$rate_to);
        }

        return ResponseFormatter::success(
            $craft->paginate($limit),
            'Data list produk berhasil diambil'
        );
        
    }
}
