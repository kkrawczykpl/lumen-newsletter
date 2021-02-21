<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsletterResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Newsletter;

class NewsletterController extends Controller
{

    /**
     * Show all subscribers data.
     *
     * @return Response
     */
    public function showAllNewsletters()
    {
        return response()->json(NewsletterResource::collection(Newsletter::all()), 200);
    }

    /**
     * Show the newsletter data by the given id.
     *
     * @param  int  $id
     * @return Response
     */
    public function showOneNewsletter($id)
    {
        return response()->json(new NewsletterResource(Newsletter::find($id)), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'required|email|max:100|unique:newsletters',
            'name' => 'required|string|max:64'
        ]);
        $newsletter = new Newsletter();
        $newsletter->email = $request->input('email');
        $newsletter->name = $request->input('name');
        $newsletter->user_token = Str::random(40);
        if($newsletter->save()) {
            // @TODO - Send E-Mail about subscribing newsletter.
            return response(["status" => true, "message" => "Thank you for registering for the newsletter!"], 201);
        }else{
            return response(["status" => false, "message" => $validator], 422);
        }
    }

    /**
     * Updates an newsletter data
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $validator = $this->validate($request, [
            'name' => 'string',
            'user_token' => 'string|min:40|max:40'
        ]);
        
        if($validator)
        {
            $newsletter = Newsletter::where('user_token', $request->input('user_token'))->firstorfail();
            $newsletter->update($request->all());
        }else{
            return response(["status" => false, "message" => "Unauthorized"]);
        }

        return response(["status" => true, "message" => "Updated successfully"], 200);
    }

    /**
     * Unsubscribe a user from Newsletter 
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy(Request $request)
    {
        $validator = $this->validate($request, [
            'user_token' => 'required|string|min:40|max:40'
        ]);

        $newsletter = Newsletter::where('user_token', $request->input('user_token'))->firstOrFail();
        // @TODO - Send E-Mail about deleting record
        if($newsletter->delete()) {
            return response(["status" => true, "message" => "You have unsubscribed from the newsletter."], 200);
        }else{
            return response(["status" => false, "message" => sprintf("Error occured! Validation errors: %s", $validator)], 422);
        }
        
    }
    //
}
