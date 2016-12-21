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

   public function prestashopGetProductsDetails(){
      try{

         $this->opt = ['resource' => 'carts', 'filter[id]' => 231, 'display' => 'full'];
         $this->xml = Prestashop::get($this->opt);
         $this->products = $this->xml->children()->children()->children()->associations->children()->cart_rows->children();

         foreach($this->products as $key => $product){
            $this->opt = ['resource' => 'products', 'display' => 'full', 'filter[id]' => (int)$product->id_product];
            $this->productDetailed = Prestashop::get($this->opt);

            $this->productNames .= (string)$this->productDetailed->products->product->meta_description->language . ' | '; //Nombre
            $this->productReferences .= (string)$this->productDetailed->products->product->reference . ' | '; //Referencia
            $this->productPrices .= (int)$this->productDetailed->products->product->price . ' | '; //Puntos
         }

         $this->returnProducts = json_decode(json_encode([
            'names'=>$this->productNames,
            'references'=>$this->productReferences,
            'prices'=>$this->productPrices,
         ]));

         return dd($this->returnProducts);
      }catch(Exception $e){
         return dd($e);
      }
   }


   /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
   public function index()
   {
     //
   }

   /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
   public function create()
   {
     //
   }

   /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
   public function store(Request $request)
   {
     //
   }

   /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
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
   public function edit($id)
   {
     //
   }

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
