<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShareImageController extends Controller
{
    //

    public function shareImage(Request $request){
        // Validate the file
        $request->validate([
            'file' => 'required|file|mimes:jpg,png,pdf|max:2048', // Adjust validation as needed
        ]);

        // Store the file
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('uploads', 'public'); // Store in the public disk
            return response()->json([
                'message' => 'File uploaded successfully',
                'file_path' => $filePath
                ], 200);
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }
}
