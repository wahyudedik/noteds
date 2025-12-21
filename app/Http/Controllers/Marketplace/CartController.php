<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        // For now, cart is handled client-side
        // In future, can implement server-side cart with session/database
        return Inertia::render('Marketplace/Cart');
    }
}
