<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data , $message = null , $code = 200)
    {
        return response()->json([
            'success' => true ,
            'message' => $message ,
            'data' => $data ,
            'code' => $code ,

        ], $code);
    }


    public function error($data , $message=null , $code =500)
    {
        return response()->json([
            'status' => false ,
            'message' => $message ,
            'data' => $data ,
            'code' => $code
        ],$code);
    }

     protected function paginated($data, string $message = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ]);
    }
}
