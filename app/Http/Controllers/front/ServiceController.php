<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    //returns all active services
    public function index(){
      $services =   Service::where('status', 1)->orderBy('created_at', 'DESC')->get();
      return $services;
    }

    //latest services
    public function latestServices (Request $request){
      $services =   Service::where('status', 1)->take($request->get('limit'))->orderBy('created_at', 'DESC')->limit(3)->get();
      return $services;
    }

    public function service($id){
      $service = Service::find($id);
      if ($service == null) {
        # code...
        return response()->json(['message' => 'Service not found'], 404);
      }
      return $service;
    }
}
