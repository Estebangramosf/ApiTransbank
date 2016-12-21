<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Prestashop;

class PrestashopController extends Controller
{

   public function test(){
      //dd(Prestashop::class);


      //$opt['resource'] = 'products';
      //$opt['display'] = 'full';

      //$opt['resource'] = 'order_details';
      //$opt['display'] = 'full';
      //$opt['id'] = 20;

      $opt = array('resource' => 'carts', 'filter[id]' => 220, 'display' => 'full');


      $xml = Prestashop::get($opt);

      $result = $xml->children()->children()->children()->associations->children()->cart_rows->children();
      foreach($result as $item){
         //dd($item);
         $opt = array('resource' => 'products', 'filter[id]' => $item->product_id, 'display' => 'full');
         $xml = Prestashop::get($opt);
         dd($xml);

      }



      //dd($xml->children()->children()->children()->associations->children()->cart_rows->children());
      //dd($xml->children());
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
