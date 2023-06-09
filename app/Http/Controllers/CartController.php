<?php

namespace App\Http\Controllers;
use DB;
use App\Models\Cart;
use App\Models\CartDetail;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class CartController extends Controller
{
    public function create(Request $request){
        
        $request->validate([
            'quantity' => 'integer|min:1|max:100',
        ]);
        try{
            DB::beginTransaction();
            $cart = Cart::firstOrCreate([
                'user_id' => $request->session()->get('USER_ID'),
            ]);
            
            if(CartDetail::where('product_id',$request->id)->where('cart_id' , $cart->id)->where('size',$request->size)->where('color',$request->color)->get()->count() == 1){
                $model = CartDetail::where('product_id',$request->id)->first();
                $model->quantity += $request->quantity;
                $model->update();
                
            }else{
                CartDetail::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->id,
                    'quantity' => $request->quantity,
                    'price' => $request->price,
                    'size' => $request->size,
                    'color' => $request->color,
                ]);

                
            }
            
            DB::commit();
            return 'true';
        }catch(Exception $e){
            DB::rollBack();
        }
            
        return 'false';
        
    }

    public function minus_quantity(Request $request){
        $model = CartDetail::find($request->id);
        $model->quantity -= 1;
        $model->update();
        return number_format($model->quantity * $model->price);
    }

    public function plus_quantity(Request $request){
        $model = CartDetail::find($request->id);
        $model->quantity += 1;
        $model->update();
        return number_format($model->quantity * $model->price);
    }


    public function remove_item_cart(Request $request){
        $model = CartDetail::find($request->id);
        $model->delete();
        return 'Successfully updated';
    }
    
}
