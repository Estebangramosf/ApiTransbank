<?php

namespace App\Http\Controllers;

use App\HistorialCanje;
use App\User;
use App\WebpayPago;
use Illuminate\Http\Request;

use App\Http\Requests;

class ReportController extends Controller
{
    private $users;
    private $transactions;
    private $payments;
    public function users(){
      $this->users = User::all();
      return view('reports.users', ['users'=>$this->users]);
    }
    public function transactions(){
      $this->transactions = HistorialCanje::all();
      return view('reports.transactions', ['transactions'=>$this->transactions]);
    }
   public function payments(){
      $this->payments = WebpayPago::all();
      return view('reports.payments', ['payments'=>$this->payments]);
   }
}
