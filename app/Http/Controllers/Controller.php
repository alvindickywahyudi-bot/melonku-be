<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function userAccessTokenId($access_token){
        // break up the string to get just the token
        $auth_header = explode(' ', $access_token);
        $token = $auth_header[sizeof($auth_header) - 1];
        // break up the token into its three parts
        // dd($token);
        $token_parts = explode('.', $token);
        $token_header = $token_parts[1];
        // base64 decode to get a json string
        $token_header_json = base64_decode($token_header);
        // you'll get this with the provided token:
        // {"typ":"JWT","alg":"RS256","jti":"9fdb0dc4382f2833ce2d3993c670fafb5a7e7b88ada85f490abb90ac211802720a0fc7392c3f2e7c"}
        // then convert the json to an array
        $token_header_array = json_decode($token_header_json, true);

        $user_token = $token_header_array['jti'];
        $user = DB::table('oauth_access_tokens')->where('id', $user_token)->first();

        return $user;
    }
}
