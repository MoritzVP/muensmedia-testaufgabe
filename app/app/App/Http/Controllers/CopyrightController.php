<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class CopyrightController extends Controller
{
    public const HEADER = "
      /   _____________________         
     /__|   |  _ \  .  / | |\ |
        |   | |_| \/ \/  | | \|
        ";

    // Generated with  https://patorjk.com/software/taag/
    public const COPYRIGHT = "
    by Moritz von Pokrzywnicki :)
        ";

    static public function getCopyright(): string
    {
        return self::HEADER . self::COPYRIGHT;
    }

    static public function showCopyright(): Response
    {
        return response( self::getCopyright() )->header('Content-Type', 'text/plain');
    }
}
