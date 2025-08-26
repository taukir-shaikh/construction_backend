<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
     public function index(){
      $testimonial =   Testimonial::where('status', 1)->orderBy('created_at', 'DESC')->get();
      return $testimonial;
    }

    //latest services
    public function latestTestimonials (Request $request){
      $testimonial =   Testimonial::where('status', 1)->take($request->get('limit'))->orderBy('created_at', 'DESC')->limit(3)->get();
      return $testimonial;
    }
}
