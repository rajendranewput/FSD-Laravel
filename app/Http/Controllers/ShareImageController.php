<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Storage;

class ShareImageController extends Controller
{
    //

    public function shareImage(Request $request){
        // Validate the file

        // Store the file
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public'); // Store in the public disk
            $url = Storage::disk('public')->url($filePath);
            $details = [
                'title' => 'Mail From Laravel',
                'body' => $url
            ];
            $mail = Mail::to('hemlata@newput.com')->send(new SendMail($details));
           
            return response()->json([
                'message' => 'Email sent successfully',
                'file_path' => $url
                ], 200);
        }
        
        return response()->json(['error' => 'File not uploaded'], 400);
    }
}
