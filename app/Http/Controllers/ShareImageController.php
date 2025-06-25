<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Storage;

/**
 * Share Image Controller
 * 
 * @package App\Http\Controllers
 * @version 1.0
 */
class ShareImageController extends Controller
{
    /**
     * Share Image via Email
     * 
     * @param Request $request The incoming HTTP request containing the image file
     * @return JsonResponse JSON response with file URL or error message
     * 
     * @api {get} /image-share Share Image
     * @apiName ShareImage
     * @apiGroup ShareImage
     * @apiParam {File} file Image file to be shared
     * @apiSuccess {String} data File download URL
     * @apiError {String} message Error message if file upload fails
     */
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
           
            return $this->successResponse([
                'file_path' => $url
            ], 'Email sent successfully');
        }
        
        return $this->badRequestResponse('File not uploaded');
    }
}
