<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

use App\Http\Requests;
use Prestashop;

class PrestashopController extends Controller
{

   //dd(Prestashop::class);

   //$opt['resource'] = 'products';
   //$opt['display'] = 'full';

   //$opt['resource'] = 'order_details';
   //$opt['display'] = 'full';
   //$opt['id'] = 20;

   private $opt;
   private $xml;

   private $products;
   private $productDetailed;

   private $productNames;
   private $productReferences;
   private $productPrices;

   private $returnProducts=[];

   public function prestashopGetProductsDetails($cart_id){
      try{

         $this->opt = ['resource' => 'carts', 'filter[id]' => $cart_id, 'display' => 'full'];
         $this->xml = Prestashop::get($this->opt);
         $this->products = $this->xml->children()->children()->children()->associations->children()->cart_rows->children();

         foreach($this->products as $key => $product){
            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);

            //DEPRECATED
            //$this->productNames .= (string)$this->productDetailed->products->product->meta_description->language . ' | '; //Nombre
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

         foreach($this->products as $key => $product){

            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);
            dd($this->productDetailed);

            $this->opt = ['resource' => 'stock_availables', 'display' => '[id,quantity]', 'filter[id]' => (int)$product->id_product];
            $this->productStock = Prestashop::get($this->opt);
            dd($this->productStock);
            //DEPRECATED
            //$this->productNames .= (string)$this->productDetailed->products->product->meta_description->language . ' | '; //Nombre
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
}
