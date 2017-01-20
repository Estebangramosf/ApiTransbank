<?php

namespace App\Http\Controllers;

use App\PrestashopProduct;
use App\PrestashopProductStock;
use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Prestashop;

class PrestashopController extends Controller
{
   private $opt;
   private $xml;

   private $products;
   private $productDetailed;
   private $productStock;

   private $productNames;
   private $productReferences;
   private $productPrices;

   private $product_id;
   private $product;

   private $returnProducts=[];

   public function prestashopGetProductsDetails($cart_id){
      try{
         $this->opt = ['resource' => 'carts', 'filter[id]' => $cart_id, 'display' => 'full'];
         $this->xml = Prestashop::get($this->opt);
         $this->products = $this->xml->children()->children()->children()->associations->children()->cart_rows->children();
         foreach($this->products as $key => $product){
            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);
            $this->productNames .= (string)$this->productDetailed->products->product->name->language . ' | '; //Nombre
            $this->productReferences .= (string)$this->productDetailed->products->product->reference . ' | '; //Referencia
            $this->productPrices .= (int)$this->productDetailed->products->product->price . ' | '; //Puntos
         }
         $this->returnProducts = json_decode(json_encode([
            'names'=>$this->productNames,
            'references'=>$this->productReferences,
            'prices'=>$this->productPrices,
         ]));
         return $this->returnProducts;
      }catch(Exception $e){
         return dd($e);
      }
   }

   public function prestashopGetProducts($cart_id){
      try{
         $this->opt = ['resource' => 'carts', 'filter[id]' => $cart_id, 'display' => 'full'];
         $this->xml = Prestashop::get($this->opt);
         $this->products = $this->xml->children()->children()->children()->associations->children()->cart_rows->children();

         //Todos y cada uno de los productos pedidos, vienen alreves en
         //$this->products

         //Por lo tanto se invierte el orden al orden de selección en PS
         foreach((array)$this->products as $product){
            $this->products = array_reverse($product);
         }

         foreach($this->products as $key => $product){
            //Contiene cada producto con pocos datos, id,cantidad
            //dd($product);

            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);

            //Contiene el producto en detalle
            //dd($this->productDetailed);

            $this->opt = ['resource' => 'stock_availables', 'display' => '[id,quantity]', 'filter[id]' => (int)$product->id_product];
            $this->productStock = Prestashop::get($this->opt);

            //Contiene el stock del producto
            //dd($this->productStock);

            $this->product_id = (int)$this->productDetailed->products->product->id;
            $this->productStock = (int)$this->productStock->stock_availables->stock_available->quantity;

            //Contiene todos los parametros para enviar al webservice de prestashop
            //dd([(string)$this->product_id,(int)$product->quantity,$this->productStock,$cart_id]);

            // producto_id_aux , cantidad_compra , stock_real , orden_compra_id
            try{
               $result = DB::select('call manage_product_stocks(?,?,?,?)',
                  [(string)$this->product_id,
                     (int)$product->quantity,
                     $this->productStock,
                     $cart_id]);
               dd($result);
            }catch(Exception $e){
               dd($e);
            }



            //dd($result);

            //$this->product = PrestashopProduct::where('producto_id',$this->product_id)->first();
            //dd();
            /*
                        $newProduct = PrestashopProduct::create([
                           'orden_compra_id'=>1,
                           'carro_id'=>1,
                           'producto_id'=>1,
                           'cantidad_compra'=>1,
                           'estado_orden_compra'=>'enproceso',
                        ]);



                        $newProduct = PrestashopProduct::where('id',$newProduct->id)->lockForUpdate()->get();

                        $newProduct[0]->cantidad_compra = 4;
                        $newProduct[0]->save();

                        dd($newProduct);

                        if(count($this->product)>0){

                           //dd('ya existe, se actualiza con los nuevos valores');
                           //dd($this->product);
                           //Valida el stock
                           //Maneja el estado del stock




                        }elseif($this->product<1 && $this->productStock>0){
                           //dd('no existe, se crea');

                           $newProduct = PrestashopProduct::create([
                              'orden_compra_id'=>$cart_id,
                              'carro_id'=>$cart_id,
                              'producto_id'=>$this->product_id,
                              'cantidad_compra'=>(int)$this->products[$key+1]->quantity,
                              'estado_orden_compra'=>'enproceso',
                           ]);
                           $newProduct = PrestashopProduct::find($newProduct->id)->sharedLock()->get();

                           dd();


                           $newProductStock = PrestashopProductStock::create([
                              'producto_id'=>$this->product_id ,
                              'stock'=>$this->productStock,
                              'estado_producto'=>'con_stock'
                           ]);

                        }
            */
            //dd();




         }

         /*
         dd([
            'productStock'=>$this->productStock,
            'productDetailed'=>$this->productDetailed
         ]);
         $this->returnProducts = json_decode(json_encode([
            'productStock'=>$this->productStock,
            'productDetailed'=>$this->productDetailed
         ]));
         */
         dd('ok');
         //return $this->returnProducts;
      }catch(Exception $e){
         return dd($e);
      }
   }


   /*


      public function prestashopGetProducts($cart_id){
      try{
         $this->opt = ['resource' => 'carts', 'filter[id]' => $cart_id, 'display' => 'full'];
         $this->xml = Prestashop::get($this->opt);
         $this->products = $this->xml->children()->children()->children()->associations->children()->cart_rows->children();
         foreach($this->products as $key => $product){
            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);
            $this->opt = ['resource' => 'stock_availables', 'display' => '[id,quantity]', 'filter[id]' => (int)$product->id_product];
            $this->productStock = Prestashop::get($this->opt);
            $this->product_id = (int)$this->productDetailed->products->product->id;
            $this->productStock = (int)$this->productStock->stock_availables->stock_available->quantity;


            // producto_id_aux , cantidad_compra , stock_real , orden_compra_id
            DB::select('call manage_product_stocks(1,5,0,"1")');
            DB::select('call manage_product_stocks(1,5,5,"2")');

            $this->product = PrestashopProduct::where('producto_id',$this->product_id)->first();



            dd();

            $newProduct = PrestashopProduct::create([
               'orden_compra_id'=>1,
               'carro_id'=>1,
               'producto_id'=>1,
               'cantidad_compra'=>1,
               'estado_orden_compra'=>'enproceso',
            ]);





            $newProduct = PrestashopProduct::where('id',$newProduct->id)->lockForUpdate()->get();

            $newProduct[0]->cantidad_compra = 4;
            $newProduct[0]->save();

            dd($newProduct);

            if(count($this->product)>0){

               //dd('ya existe, se actualiza con los nuevos valores');
               //dd($this->product);
               //Valida el stock
               //Maneja el estado del stock




            }elseif($this->product<1 && $this->productStock>0){
               //dd('no existe, se crea');

               $newProduct = PrestashopProduct::create([
                  'orden_compra_id'=>$cart_id,
                  'carro_id'=>$cart_id,
                  'producto_id'=>$this->product_id,
                  'cantidad_compra'=>(int)$this->products[$key+1]->quantity,
                  'estado_orden_compra'=>'enproceso',
               ]);
               $newProduct = PrestashopProduct::find($newProduct->id)->sharedLock()->get();

               dd();


               $newProductStock = PrestashopProductStock::create([
                  'producto_id'=>$this->product_id ,
                  'stock'=>$this->productStock,
                  'estado_producto'=>'con_stock'
               ]);

            }
            //dd();




         }

         dd([
            'productStock'=>$this->productStock,
            'productDetailed'=>$this->productDetailed
         ]);
         $this->returnProducts = json_decode(json_encode([
            'productStock'=>$this->productStock,
            'productDetailed'=>$this->productDetailed
         ]));

return $this->returnProducts;
}catch(Exception $e){
   return dd($e);
}
   }






   */
}
